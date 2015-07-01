<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Enums\PortableTypes;
use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Provisioners\ProvisioningRequest;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Support\SnapshotManifest;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Database\Models\RouteHash;
use DreamFactory\Enterprise\Database\Models\Snapshot;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Library\Utility\Inflector;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

/**
 * Snapshot services
 */
class SnapshotService extends BaseService
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use Archivist, EntityLookup, Notifier;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Creates an export of a instance
     *
     * @param string     $instanceId  The instance id
     * @param Filesystem $destination The destination upon which to place the export.
     *                                If null, the instance's snapshot storage area is used.
     * @param int        $keepDays    The number of days to keep the snapshot
     *
     * @return array
     * @throws \Exception
     */
    public function create($instanceId, Filesystem $destination = null, $keepDays = 30)
    {
        //  Build our "mise en place", as it were...
        $_success = false;
        $_stamp = date('YmdHis');

        //  Create the snapshot ID
        $_instance = $this->_findInstance($instanceId);
        $_instanceName = $_instance->instance_name_text;
        $_snapshotId = $_stamp . '.' . Inflector::neutralize($_instanceName);

        $_snapshotName = str_replace('{id}', $_snapshotId, config('snapshot.templates.snapshot-file-name'));

        //  Make our temp path...
        $_workPath = static::getWorkPath($_snapshotId, true) . DIRECTORY_SEPARATOR;

        //  Create the snapshot archive and stuff it full of goodies
        $_fsSnapshot = new Filesystem(new ZipArchiveAdapter($_workPath . $_snapshotName));

        //  Get a route hash...
        $_routeHash = RouteHashing::create($_snapshotName, $keepDays);
        $_routeLink = '//' .
            str_replace(['http://', 'https://', '//'],
                null,
                rtrim(config('snapshot.hash-link-base'), ' /')) . '/' . $_routeHash;

        //  Create our manifest
        $_manifest = new SnapshotManifest([
            'id'                  => $_snapshotId,
            'name'                => $_snapshotName,
            'timestamp'           => $_stamp,
            'guest-location'      => $_instance->guest_location_nbr,
            'instance-id'         => $_instance->instance_id_text,
            'cluster-id'          => (int)$_instance->cluster_id,
            'db-server-id'        => (int)$_instance->db_server_id,
            'web-server-id'       => (int)$_instance->web_server_id,
            'app-server-id'       => (int)$_instance->app_server_id,
            'owner-id'            => (int)$_instance->user->id,
            'owner-email-address' => $_instance->user->email_addr_text,
            'owner-storage-key'   => $_instance->user->storage_id_text,
            'storage-key'         => $_instance->storage_id_text,
            'hash'                => $_routeHash,
            'link'                => $_routeLink,
        ], config('snapshot.metadata-file-name'), $_fsSnapshot);

        //  Grab the list of portable services from the provisioner
        $_services = Provision::getPortableServices($_instance->guest_location_nbr);

        try {
            //  Loop through the returned service list
            foreach ($_services as $_type => $_service) {
                //  The portability service will append the appropriate suffix for the type of export
                $_to = $_workPath . $_snapshotId . '.' . $_type;
                $_request = new ProvisioningRequest($_instance);

                /**
                 * Call each of the portable services and add the resultant export to the master export file
                 */
                if (false !== ($_outfile = $_service->export($_request, $_to))) {
                    $_metadata[$_type . '-export'] = $_outfile;
                    static::moveWorkFile($_fsSnapshot, $_workPath . $_outfile);
                }
            }

            try {
                //  Write our snapshot manifesto
                $_manifest->write();

                //  Close up the files
                /** @noinspection PhpUndefinedMethodInspection */
                $this->flushZipArchive($_fsSnapshot);

                //  Move the snapshot archive into the "snapshots" private storage area
                static::moveWorkFile($destination ?: $_instance->getSnapshotMount(), $_workPath . $_snapshotName);

                //  Generate a record for the dashboard
                $_routeHash = RouteHash::byHash($_routeHash)->first();

                //  Create our snapshot record
                Snapshot::create([
                    'user_id'          => $_instance->user_id,
                    'instance_id'      => $_instance->id,
                    'route_hash_id'    => $_routeHash->id,
                    'snapshot_id_text' => $_snapshotId,
                    'public_ind'       => true,
                    'public_url_text'  => $_routeLink,
                    'manifest_text'    => $_manifest->toArray(),
                    'expire_date'      => $_routeHash->expire_date,
                ]);

                //  Let the user know...
                $this->notifyInstanceOwner($_instance,
                    'Instance export successful',
                    [
                        'firstName'     => $_instance->user->first_name_text,
                        'headTitle'     => 'Export Complete',
                        'contentHeader' => 'Your export has completed',
                        'emailBody'     => <<<HTML
<p>Your export has been created successfully.  You can download it for up to {$keepDays} days by going to
<a href="{$_routeLink}" target="_blank">{$_routeLink}</a> from any browser.</p>
HTML
                        ,
                    ]);

                $_success = true;
            } catch (\Exception $_ex) {
                $this->error('exception building snapshot archive: ' . $_ex->getMessage());
            }
        } catch (\Exception $_ex) {
            $this->error('exception during sub-provisioner export call: ' . $_ex->getMessage());
        } finally {
            //  Cleanup
            $_fsSnapshot = null;
            static::deleteWorkPath($_snapshotId);
        }

        return $_success;
    }

    /**
     * Given an instance and a snapshot ID, replace the data with that of the snapshot.
     *
     * @param string $snapshotId
     *
     * @return bool
     */
    public function restore($snapshotId)
    {
        //  Mount the snapshot
        $_workPath = static::getWorkPath('restore.' . $snapshotId) . DIRECTORY_SEPARATOR;
        $_fsSnapshot = $this->mountSnapshot($snapshotId, $_workPath);
        $_workFile = $_workPath . config('snapshot.metadata-file-name');

        //  Reconstitute the manifest and grab the services
        $_snapshot = SnapshotManifest::create(json_decode($_workFile));
        $_services = \Provision::getPortableServices($_snapshot->get('guest-location'));

        //  Try and locate the instance
        $_instanceId = $_snapshot->get('instance-id');
        if (false === ($_instance = $this->_locateInstance($_instanceId))) {
            throw new \InvalidArgumentException('Instance "' .
                $_instanceId .
                '" is not eligible to be an import target.');
        }

        $_request = new ProvisioningRequest($_instance);
        $_result = [];

        foreach ($_fsSnapshot->listContents() as $_item) {
            //  Find the exports in the snapshot
            if (false !== ($_pos = stripos('-export.', $_item['file']))) {
                if (!PortableTypes::has($_type = substr($_item['file'], 0, $_pos))) {
                    continue;
                }

                if (array_key_exists($_type, $_services)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $_result[$_type] =
                        $_services[$_type]->import($_request, $_workPath . DIRECTORY_SEPARATOR . $_item['file']);
                }
            }
        }

        return $_result;
    }

    /**
     * Locates and mounts a snapshot
     *
     * @param string      $snapshotId
     * @param string|null $workPath
     *
     * @return Filesystem|string If $workPath is specified, the Filesystem is returned, otherwise the path to the extracted files.
     */
    protected function mountSnapshot($snapshotId, $workPath = null)
    {
        $_workPath = $workPath ?: static::getWorkPath($snapshotId);

        $_model = $this->_findSnapshot($snapshotId);
        $_url = 'http://' . str_ireplace(['http://', 'https://', '//', '://'], null, $_model->public_url_text);
        $_workFile = $_workPath . DIRECTORY_SEPARATOR . $_model->snapshot_id_text . '.zip';

        if (!$this->getSnapshotFromUrl($_url, $_workFile)) {
            throw new \InvalidArgumentException('Error downloading snapshot "' . $snapshotId . '"');
        }

        $_zip = new \ZipArchive();
        $_zip->open($_workFile);
        $_zip->extractTo($_workPath);
        $_zip->close();
        $_zip = null;

        //  Delete our work file, leaving contents only
        @unlink($_workFile);

        return $workPath ? $_workPath : new Filesystem(new ZipArchiveAdapter($_workFile));
    }

    /**
     * Downloads a file from $url and stores in $path
     *
     * @param string $url
     * @param string $path
     *
     * @return bool
     */
    protected function getSnapshotFromUrl($url, $path)
    {
        if (false !== ($_source = fopen($url, 'rb')) && false !== ($_target = fopen($path, 'wb'))) {
            while (!feof($$_source)) {
                fwrite($_target, fread($_source, 1024 * 8), 8192);
            }

            fclose($_source);
            fclose($_target);

            return true;
        }

        return false;
    }
}