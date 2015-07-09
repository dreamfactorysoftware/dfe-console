<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Enums\PortableTypes;
use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceRequest;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Support\SnapshotManifest;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\RouteHash;
use DreamFactory\Enterprise\Database\Models\Snapshot;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\ExportJob;
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
        //  Create the snapshot ID
        $_instance = $this->_findInstance($instanceId);

        return static::createFromExports(
            $_instance,
            Provision::export(new ExportJob($instanceId)),
            $destination,
            $keepDays
        );
    }

    /**
     * Creates an export of a instance
     *
     * @param Instance   $instance    The instance of the exports
     * @param array      $exports     Array of files to include in the snapshot
     * @param Filesystem $destination The destination upon which to place the export. Currently unused
     *                                If null, the instance's snapshot storage area is used.
     * @param int        $keepDays    The number of days to keep the snapshot
     *
     * @return array
     */
    public function createFromExports(Instance $instance, array $exports, Filesystem $destination = null, $keepDays = 30)
    {
        //  Build our "mise en place", as it were...
        $_success = false;
        $_stamp = date('YmdHis');

        //  Create the snapshot ID
        $_snapshotId = $_stamp . '.' . Inflector::neutralize($instance->instance_name_text);
        $_snapshotName = str_replace('{id}', $_snapshotId, config('snapshot.templates.snapshot-file-name'));

        //  Get our storage location
        $_workPath =
            config('provisioning.storage-root') . DIRECTORY_SEPARATOR . $instance->getSnapshotPath() . DIRECTORY_SEPARATOR;

        //  Get a route hash...
        $_routeHash = RouteHashing::create($_snapshotName, $keepDays);
        $_routeLink = config('snapshot.hash-link-protocol', 'https') . '://' .
            str_replace(['http://', 'https://', '//'],
                null,
                rtrim(config('snapshot.hash-link-base'), ' /')) . '/' . $_routeHash;

        //  Create the snapshot archive and stuff it full of goodies
        $_fsSnapshot = new Filesystem(new ZipArchiveAdapter($_workPath . $_snapshotName));

        //  Create our manifest
        $_manifest = new SnapshotManifest([
            'id'                  => $_snapshotId,
            'name'                => $_snapshotName,
            'timestamp'           => $_stamp,
            'guest-location'      => $instance->guest_location_nbr,
            'instance-id'         => $instance->instance_id_text,
            'cluster-id'          => (int)$instance->cluster_id,
            'db-server-id'        => (int)$instance->db_server_id,
            'web-server-id'       => (int)$instance->web_server_id,
            'app-server-id'       => (int)$instance->app_server_id,
            'owner-id'            => (int)$instance->user->id,
            'owner-email-address' => $instance->user->email_addr_text,
            'owner-storage-key'   => $instance->user->storage_id_text,
            'storage-key'         => $instance->storage_id_text,
            'hash'                => $_routeHash,
            'link'                => $_routeLink,
        ], config('snapshot.metadata-file-name'), $_fsSnapshot);

        try {
            //  Loop through the export list
            foreach ($exports as $_type => $_export) {
                $_metadata[$_type . '-export'] = basename($_export);
                static::moveWorkFile($_fsSnapshot, $_workPath . $_export);
            }

            try {
                //  Write our snapshot manifesto
                $_manifest->write();

                //  Close up the files
                /** @noinspection PhpUndefinedMethodInspection */
                $this->flushZipArchive($_fsSnapshot);

                //  Generate a record for the dashboard
                $_routeHash = RouteHash::byHash($_routeHash)->first();

                //  Create our snapshot record
                Snapshot::create([
                    'user_id'          => $instance->user_id,
                    'instance_id'      => $instance->id,
                    'route_hash_id'    => $_routeHash->id,
                    'snapshot_id_text' => $_snapshotId,
                    'public_ind'       => true,
                    'public_url_text'  => $_routeLink,
                    'expire_date'      => $_routeHash->expire_date,
                ]);

                //  Let the user know...
                $this->notifyInstanceOwner($instance,
                    'Instance export successful',
                    [
                        'firstName'     => $instance->user->first_name_text,
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

        $_request = new ProvisionServiceRequest($_instance);
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