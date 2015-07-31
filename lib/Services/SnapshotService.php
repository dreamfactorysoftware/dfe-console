<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Enums\PortableTypes;
use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceRequest;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Support\SnapshotManifest;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Common\Utility\Disk;
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

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The working directory being used
     */
    protected $workPath;

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
    public function create($instanceId, Filesystem $destination = null, $keepDays = EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP)
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
     * @param string $exportId
     * @param string $filename
     * @param array  $manifest
     * @param int    $keepDays
     *
     * @return array
     */
    protected function createExportArchive($exportId, $exportName, array $manifest = [], $keepDays = EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP)
    {
        list($_hash, $_link) = $this->createDownloadHash($exportName, $keepDays);
        $_fsSnapshot = new Filesystem(new ZipArchiveAdapter($this->workPath . $exportName));

        //  Create our manifest
        $_manifest = new SnapshotManifest(
            array_merge(
                $manifest,
                [
                    'id'   => $exportId,
                    'name' => $exportName,
                    'hash' => $_hash,
                    'link' => $_link,
                ]
            ),
            config('snapshot.metadata-file-name'),
            $_fsSnapshot
        );

        return [$_fsSnapshot, $_manifest, $_hash, $_link];
    }

    /**
     * @param array                        $files
     * @param \League\Flysystem\Filesystem $archive
     *
     * @return array
     */
    protected function addFilesToArchive(array $files, Filesystem $archive)
    {
        $_added = [];

        foreach ($files as $_type => $_export) {
            $_added[$_type . '-export'] = $_export;
            static::moveWorkFile($archive, $$this->workPath . $_export);
        }

        return $_added;
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
    public function createFromExports(Instance $instance, array $exports, Filesystem $destination = null, $keepDays = EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP)
    {
        //  Build our "mise en place", as it were...
        $_success = false;
        $_stamp = date('YmdHis');

        //  Create the snapshot ID
        $_snapshotId = $_stamp . '.' . Inflector::neutralize($instance->instance_name_text);
        $_snapshotName = str_replace('{id}', $_snapshotId, config('snapshot.templates.snapshot-file-name'));

        //  Set up the temp dir
        $this->setWorkPath($instance->getSnapshotPath());

        //  Create the snapshot archive and stuff it full of goodies
        /** @var SnapshotManifest $_manifest */
        list($_fsSnapshot, $_manifest, $_routeHash, $_routeLink) = $this->createExportArchive(
            $_snapshotId,
            $_snapshotName,
            [
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
            ],
            $keepDays
        );

        try {
            $this->addFilesToArchive($exports, $_fsSnapshot);

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
     * @param int|string $instanceId
     * @param string $snapshotId
     *
     * @return bool
     */
    public function restore($instanceId, $snapshotId)
    {
        //  Mount the snapshot
        $_tag = 'restore.' . $snapshotId;
        $_workPath = static::getWorkPath($_tag) . DIRECTORY_SEPARATOR;
        $_fsSnapshot = $this->mountSnapshot($snapshotId, $_workPath);
        $_workFile = $_workPath . config('snapshot.metadata-file-name');

        //  Reconstitute the manifest and grab the services
        $_snapshot = SnapshotManifest::create(json_decode($_workFile));
        $_services = \Provision::getPortableServices($_snapshot->get('guest-location'));

        //  Try and locate the instance
        $_instanceId = $_snapshot->get('instance-id');
        if (false === ($_instance = $this->_locateInstance($_instanceId))) {
            static::deleteWorkPath($_tag);
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

    /**
     * @param string|null $append
     *
     * @throws \DreamFactory\Enterprise\Common\Exceptions\DiskException
     */
    protected function setWorkPath($append = null)
    {
        $this->workPath = Disk::path([config('provisioning.storage-root'), $append]) . DIRECTORY_SEPARATOR;
    }
}