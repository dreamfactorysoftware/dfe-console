<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Enums\PortableTypes;
use DreamFactory\Enterprise\Common\Facades\InstanceStorage;
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
use DreamFactory\Library\Utility\Disk;
use DreamFactory\Library\Utility\Exceptions\FileSystemException;
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
     * @param Instance   $instance    The instance of the exports
     * @param array      $exports     Array of files to include in the snapshot
     * @param Filesystem $destination The destination upon which to place the export. Currently unused
     *                                If null, the instance's snapshot storage area is used.
     * @param int        $keepDays    The number of days to keep the snapshot
     *
     * @return array|boolean The snapshot metadata array or false on failure.
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
        list($_fsSnapshot, $_manifest, $_routeHash, $_routeLink) =
            $this->createExportArchive($_snapshotId,
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
                $keepDays);

        try {
            $this->addFilesToArchive($exports, $_fsSnapshot);

            try {
                //  Write our snapshot manifesto
                $_manifest->write();

                //  Close up the files
                /** @noinspection PhpUndefinedMethodInspection */
                $this->flushZipArchive($_fsSnapshot);

                //  Look up the hash entry
                if (null === ($_routeHash = RouteHash::byHash($_routeHash)->first())) {
                    throw new \LogicException('Previously created route hash not found.');
                }

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

                //  Copy to $destination if requested
                if ($destination) {
                    if (false === ($_fd = fopen($this->workPath . $_snapshotName, 'r'))) {
                        throw new FileSystemException('Unable to open export file "' .
                            $this->workPath .
                            $_snapshotName .
                            '".');
                    }

                    $destination->putStream($_snapshotName, $_fd);
                    fclose($_fd);
                }

                //  Let the user know...
                $this->notifyInstanceOwner($instance,
                    'Export successful',
                    [
                        'firstName'     => $instance->user->first_name_text,
                        'headTitle'     => 'Export Complete',
                        'contentHeader' => 'Your export has completed',
                        'emailBody'     => <<<HTML
<p>Your export is complete. It may be downloaded it for up to {$keepDays} days, from the following link:<br/>
<br/>
<strong><a href="{$_routeLink}" target="_blank">{$_routeLink}</a></strong>
</p>
HTML

                        ,
                    ]);

                $_success = true;
            } catch (\Exception $_ex) {
                $this->error('exception building snapshot archive: ' . $_ex->getMessage());
                throw $_ex;
            }
        } catch (\Exception $_ex) {
            $this->error('exception during sub-provisioner export call: ' . $_ex->getMessage());

            $this->notifyInstanceOwner($instance,
                'Export failure',
                [
                    'firstName'     => $instance->user->first_name_text,
                    'headTitle'     => 'Export Failure',
                    'contentHeader' => 'Your export was not created',
                    'emailBody'     => <<<HTML
<p>The export requested did not complete properly. Please make sure your instance is up and running, then try again. If the issue persists, please contact support.</p>
HTML

                    ,
                ]);

            $_success = false;
        }
        finally {
            //  Cleanup
            $_fsSnapshot = null;
        }

        return $_success ? $_manifest->toArray() : false;
    }

    /**
     * Given an instance and a snapshot ID, replace the data with that of the snapshot.
     *
     * @param int|string $instanceId
     * @param string     $snapshot A snapshot id or path to a snapshot
     */
    public function restore($instanceId, $snapshot)
    {
        $_filename = null;

        $_instance = $this->_findInstance($instanceId);
        $snapshot = trim($snapshot, DIRECTORY_SEPARATOR . ' ');

        //  Determine source of import
        if (file_exists($snapshot)) {
            //  Absolute path
            $_filename = $snapshot;
        } else if (file_exists($_instance->getSnapshotPath() . DIRECTORY_SEPARATOR . $snapshot)) {
            //  Relative to snapshot path
            $_filename = $_instance->getSnapshotPath() . DIRECTORY_SEPARATOR . $snapshot;
        } else {
            //  A snapshot hash given, find related snapshot
            if (null === ($_snapshot = Snapshot::with('route-hash')->bySnapshotId($snapshot)->first())) {
                throw new \InvalidArgumentException('The snapshot "' . $snapshot . '" is unrecognized or invalid."');
            }

            $_filename =
                $_instance->getSnapshotPath() .
                DIRECTORY_SEPARATOR .
                trim($_snapshot->routeHash->actual_path_text, DIRECTORY_SEPARATOR . ' ');
        }

        //  Mount the snapshot
        $_workPath = dirname($_filename);
        $_fsSnapshot = $this->mountSnapshot($instanceId, $_filename, $_workPath);

        //  Reconstitute the manifest and grab the services
        $_manifest = SnapshotManifest::createFromFile(config('snapshot.metadata-file-name'), $_fsSnapshot);
        $_services = \Provision::getPortableServices($_manifest->get('guest-location'));

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

        $_request->setResult($_result);
    }

    /**
     * Locates and mounts a snapshot
     *
     * @param string      $snapshotId
     * @param string|null $snapshotFile The path to the physical import file
     * @param string|null $workPath
     *
     * @return \League\Flysystem\Filesystem|string If $workPath is specified, the Filesystem is returned, otherwise the
     *                                             path to the extracted files.
     */
    protected function mountSnapshot($snapshotId, $snapshotFile = null, $workPath = null)
    {
        $_workPath = $workPath ?: static::getWorkPath($snapshotId);

        if (null === $snapshotFile) {
            $_model = $this->_findSnapshot($snapshotId);
            $_url = 'http://' . str_ireplace(['http://', 'https://', '//', '://'], null, $_model->public_url_text);
            $_workFile = $_workPath . DIRECTORY_SEPARATOR . $_model->snapshot_id_text . '.zip';

            if (!$this->getSnapshotFromUrl($_url, $_workFile)) {
                throw new \InvalidArgumentException('Error downloading snapshot "' . $snapshotId . '"');
            }
        } else {
            $_workFile = $snapshotFile;
        }

        $_zip = new \ZipArchive();
        $_zip->open($_workFile);
        $_zip->extractTo($_workPath);
        $_zip->close();
        $_zip = null;

        //  Delete our work file, leaving contents only
        @unlink($_workFile);

        return $workPath ?: new Filesystem(new ZipArchiveAdapter($_workFile));
    }

    /**
     * Returns the absolute path to the root of the trash filesystem
     *
     * @return string
     */
    public function getRootTrashPath()
    {
        return InstanceStorage::getTrashPath();
    }

    /**
     * Returns the absolute path to the root of the trash filesystem
     *
     * @return Filesystem
     */
    public function getRootTrashMount()
    {
        return InstanceStorage::getTrashMount();
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
            while (!feof($_source)) {
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
     */
    protected function setWorkPath($append = null)
    {
        $this->workPath = Disk::path([config('provisioning.storage-root'), $append]) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $filename Path to physical entity
     * @param int    $keepDays The number of days to remain "public"
     *
     * @return array
     */
    protected function createHashedLink($filename, $keepDays = EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP)
    {
        //  Get a route hash...
        $_hash = RouteHashing::create($filename, $keepDays);

        $_link =
            config('snapshot.hash-link-protocol', 'https') .
            '://' .
            str_replace(['http://', 'https://', '//'], null, rtrim(config('snapshot.hash-link-base'), ' /')) .
            '/' .
            $_hash;

        return [$_hash, $_link];
    }

    /**
     * @param string $exportId
     * @param string $exportName
     * @param array  $manifest
     * @param int    $keepDays
     *
     * @return array
     */
    protected function createExportArchive($exportId, $exportName, array $manifest = [], $keepDays = EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP)
    {
        list($_hash, $_link) = $this->createHashedLink($exportName, $keepDays);
        $_fsSnapshot = new Filesystem(new ZipArchiveAdapter($this->workPath . $exportName));

        //  Create our manifest
        $_manifest = new SnapshotManifest(array_merge($manifest,
            [
                'id'   => $exportId,
                'name' => $exportName,
                'hash' => $_hash,
                'link' => $_link,
            ]), config('snapshot.metadata-file-name'), $_fsSnapshot);

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
            static::moveWorkFile($archive, $this->workPath . $_export);
        }

        return $_added;
    }
}