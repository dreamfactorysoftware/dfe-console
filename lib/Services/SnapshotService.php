<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Exceptions\NotImplementedException;
use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Support\SnapshotManifest;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Utility\Inflector;
use DreamFactory\Library\Utility\JsonFile;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

/**
 * Snapshot services
 */
class SnapshotService extends BaseService
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const SNAPSHOT_NAME_PATTERN = '{snapshot-id}.snapshot.{compressor}';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Creates a snapshot of a fabric-hosted instance
     *
     * @param string     $instanceId
     * @param Filesystem $destination
     * @param int        $keepDays The number of days to keep the snapshot
     *
     * @return array
     * @throws \Exception
     */
    public function create($instanceId, Filesystem $destination = null, $keepDays = 30)
    {
        //  Build our "mise en place", as it were...
        $_stamp = date('YmdHis');
        $_instance = $this->_findInstance($instanceId);
        $_instanceName = $_instance->instance_name_text;

        //  Create the snapshot ID
        $_snapshotId = $_stamp . '.' . Inflector::neutralize($_instanceName);

        //  Start building our snapshot metadata array
        $_metadata = [
            'id'                  => $_snapshotId,
            'type'                => config('snapshot.metadata-type', 'application/json'),
            'snapshot-prefix'     => $_snapshotId,
            'timestamp'           => $_stamp,
            'instance-id'         => $_instance->instance_id_text,
            'cluster-id'          => (int)$_instance->cluster_id,
            'db-server-id'        => (int)$_instance->db_server_id,
            'web-server-id'       => (int)$_instance->web_server_id,
            'app-server-id'       => (int)$_instance->app_server_id,
            'owner-id'            => (int)$_instance->user->id,
            'owner-email-address' => $_instance->user->email_addr_text,
            'owner-storage-key'   => $_instance->user->storage_id_text,
            'storage-key'         => $_instance->storage_id_text,

        ];

        $_metadata['name'] =
            $this->_getConfigValue('snapshot.templates.snapshot-file-name', $_metadata);

        $_metadata['storage-export'] =
            $this->_getConfigValue('snapshot.templates.storage-file-name', $_metadata);

        $_metadata['database-export'] =
            $this->_getConfigValue('snapshot.templates.db-file-name', $_metadata);

        $_metadata['hash'] = RouteHashing::create($_metadata['name'], $keepDays);
        $_metadata['link'] = rtrim(config('snapshot.hash_link_base'), ' /') . '/' . $_metadata['hash'];

        //  Make our temp path...
        $_workPath =
            $this->_getTempFilesystem($_instance->instance_id_text . '.' . microtime(true),
                true) . DIRECTORY_SEPARATOR;

        //  Create the snapshot archive and stuff it full of goodies
        $_fsSnapshot = new Filesystem(new ZipArchiveAdapter($_workPath . $_metadata['name']));

        //  Create a zip file of the storage directory
        if (!$this->_archivePath($_instance->getStorageMount(), $_workPath . $_metadata['storage-export'])) {
            throw new \RuntimeException('Unable to archive source file system. Aborting.');
        }

        //  Add storage zipball to the archive
        $this->moveWorkFile($_fsSnapshot, $_workPath . $_metadata['storage-export']);

        //  Pull a database backup...
        try {
            $this->exportDatabase($_instance, $_workPath . $_metadata['database-export']);
        } catch (\Exception $_ex) {
            throw new \RuntimeException('Unable to dump source database: ' . $_ex->getMessage());
        }

        //  Add the export to the snapshot
        $this->moveWorkFile($_fsSnapshot, $_workPath . $_metadata['database-export']);

        //  Create a snapshot manifesto
        $_manifest = new SnapshotManifest($_metadata, config('snapshot.metadata-file-name'), $_fsSnapshot);
        $_manifest->write();

        //  Stuff it in the snapshot
        $this->moveWorkFile($destination ?: $_instance->getSnapshotMount(), $_workPath . $_metadata['name']);
    }

    /**
     * Given an instance and a snapshot ID, replace the data with that of the snapshot.
     *
     * @param string|int $instanceId
     * @param string     $snapshot
     *
     * @return bool
     */
    public function restore($instanceId, $snapshot)
    {
        //        $_instance = $this->_validateInstance( $instanceId );
        //
        //        //	1. Grab the tarball...
        //        $_workPath = $this->_getTempFilesystem( $_workFile );
        //        $_workPath = $_workPath . DIRECTORY_SEPARATOR . $_workFile;
        //
        //        file_put_contents( $_workPath, file_get_contents( $this->download( $instanceId, $snapshot, true ) ) );
        //
        //        //	2. Crack it open and get the goodies
        //        $_import = new \PharData( $_workPath );
        //
        //        try
        //        {
        //            $_import->extractTo( dirname( $_workPath ) );
        //
        //            if ( false === ( $_snapshot = json_decode( file_get_contents( $_workPath . '/snapshot.json' ) ) ) )
        //            {
        //                throw new RestException( HttpResponse::BadRequest, 'Invalid snapshot "' . $snapshot . '"' );
        //            }
        //        }
        //        catch ( \Exception $_ex )
        //        {
        //            $this->error( 'Error extracting snapshot tarball: ' . $_ex->getMessage() );
        //
        //            $this->_killTempDirectory( $_workPath );
        //
        //            return false;
        //        }
        //
        //        //	2. Make a snapshot of the existing data first (backup)
        //        $_backup = static::create( $instanceId, true );
        //
        //        //	3. Install snapshot storage files
        //        $_command = 'cd ' . $this->getStoragePath() . '; rm -rf ./*; /bin/tar zxf ' . $_workPath . DIRECTORY_SEPARATOR . $_snapshot->storage->tarball . ' ./';
        //        $_result = exec( $_command, $_output, $_return );
        //
        //        if ( 0 != $_return )
        //        {
        //            Log::error(
        //                'Error importing storage directory of dsp "' . $instanceId . '": ' . $_result . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL
        //            );
        //            Log::error( implode( PHP_EOL, $_output ) );
        //            $this->_killTempDirectory( $_workPath );
        //
        //            return false;
        //        }
        //
        //        //	4. Drop old, Create new database for snapshot mysql data
        //        $_db = Pii::db( 'db.cumulus' );
        //
        //        if ( Provisioners::DREAMFACTORY_ENTERPRISE == $_instance->guest_location_nbr )
        //        {
        //            $_command
        //                = 'sudo -u dfadmin /opt/dreamfactory/fabric/cerberus/config/scripts/restore_snapshot_mysql.sh ' .
        //                $_instance->db_name_text .
        //                ' ' .
        //                $_workPath;
        //        }
        //        else
        //        {
        ////			$_command
        ////				= '/usr/bin/ssh ' . static::DEFAULT_SSH_OPTIONS . ' dfadmin@' . $_instanceName . DSP::DEFAULT_DSP_SUB_DOMAIN .
        ////				' \'mysqldump --delayed-insert -e -u ' . $_instance->db_user_text . ' -p' . $_instance->db_password_text . ' ' .
        ////				$_instance->db_name_text . '\' | gzip -c >' . $_workPath;
        //        }
        //
        //        $_result = exec( $_command, $_output, $_return );
        //
        //        if ( 0 != $_return )
        //        {
        //            Log::error(
        //                'Error restoring mysql dump of dsp "' . $instanceId . '": ' . $_result . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL
        //            );
        //            Log::error( implode( PHP_EOL, $_output ) );
        //            $this->_killTempDirectory( $_workPath );
        //
        //            //@TODO need to restore snapshot taken at the beginning cuz we sucked at this...
        //
        //            return false;
        //        }
        //
        //        Log::debug( 'MySQL dump restored: ' . $_workFile );
        ////
        ////		//	5. Import mysql data
        ////
        ////		$_command
        ////			= 'cd ' . $_workPath . '; ' .
        ////			'gunzip ' . $_snapshot->mysql->tarball . '; ' .
        ////			'mysql -u ' . $_db->username . ' -p' . $_db->password . ' -h ' . DSP::DEFAULT_DSP_SERVER . ' --database=' .
        ////			$_instance->db_name_text . ' < mysql.' . $snapshot . '.sql';
        ////
        ////		$_result = exec( $_command, $_output, $_return );
        ////
        ////		if ( 0 != $_return )
        ////		{
        ////			Log::error( 'Error importing mysql dump of dsp "' . $instanceId . '": ' . $_result . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL );
        ////			Log::error( implode( PHP_EOL, $_output ) );
        ////
        ////			//	Roll everything back...
        ////			$_service->deprovision(
        ////				array(
        ////					 'name'        => $_instanceName,
        ////					 'storage_key' => $_instance->storage_id_text
        ////				),
        ////				true,
        ////				$_instance
        ////			);
        ////
        ////			$this->_killTempDirectory( $_workPath );
        ////
        ////			return false;
        ////		}
        ////
        ////		//	6.	Update snapshot with import info
        ////		$_snapshot->imports[] = array(
        ////			'timestamp' => date( 'c' ),
        ////		);
        ////
        ////		$_import->addFromString( 'snapshot.json', json_encode( $_snapshot ) );
        //
        //        //	7. Cleanup
        //        $this->_killTempDirectory( $_workPath );
        //
        //        //	Import complete!!!
        //        return true;
    }

    /**
     * @param string $tag      Unique identifier for temp space
     * @param bool   $pathOnly If true, only the path is returned.
     *
     * @return \League\Flysystem\Filesystem|string
     */
    protected function _getTempFilesystem($tag, $pathOnly = false)
    {
        $_root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dfe' . DIRECTORY_SEPARATOR . $tag;

        if (!mkdir($_root, 0777, true)) {
            throw new \RuntimeException('Unable to create working directory "' . $_root . '". Aborting.');
        }

        if ($pathOnly) {
            return $_root;
        }

        //  Set our temp base
        return new Filesystem(new Local($_root));
    }

    /**
     * @param Filesystem $source  The source file system to archive
     * @param string     $zipPath The full zip file name
     *
     * @return bool|string
     */
    protected function _archivePath($source, $zipPath)
    {
        $_archive = new Filesystem(new ZipArchiveAdapter($zipPath));

        try {
            foreach ($source->listContents('', true) as $_file) {
                if ($_file['type'] == 'dir') {
                    $_archive->createDir($_file['path']);
                } elseif ($_file['type'] == 'link') {
                    $_archive->put($_file['path'], $_file['target']);
                } elseif ($_file['type'] == 'file') {
                    file_exists($_file['path']) && $this->_writeStream($_archive, $_file['path'], $_file['path']);
                }
            }

            //  Flush zip to disk
            $_archive = null;

            return $zipPath;
        } catch (\Exception $_ex) {
            return false;
        }
    }

    /**
     * @param Instance $instance
     * @param string   $dumpFile
     *
     * @return mixed
     * @throws \DreamFactory\Enterprise\Common\Exceptions\NotImplementedException
     */
    protected function exportDatabase($instance, $dumpFile)
    {
        if ($instance->guest_location_nbr != GuestLocations::DFE_CLUSTER) {
            throw new NotImplementedException();
        }

        $_service = Provision::getPortabilityProvider($instance->guest_location_nbr);

        return $_service->export(new ProvisioningRequest($instance), $dumpFile);
    }

    /**
     * @param Filesystem|\Illuminate\Contracts\Filesystem\Filesystem $archive
     * @param string                                                 $workFile
     */
    protected function moveWorkFile($archive, $workFile = null)
    {
        if ($this->_writeStream($archive, $workFile, basename($workFile))) {
            unlink($workFile);
        }

        return;
    }

    /**
     * Pulls a config value and applies replacements with automatic json conversion of non-strings
     *
     * @param string $key
     * @param array  $replacements
     *
     * @return string
     */
    protected function _getConfigValue($key, $replacements)
    {
        $_stringified = false;
        $_setting = config($key);

        if (is_array($_setting)) {
            if (false === ($_setting = JsonFile::encode($_setting))) {
                throw new \InvalidArgumentException('The value at key "' .
                    $key .
                    '" is not a string or a jsonable array.');
            }

            $_stringified = true;
        }

        //  Surround keys with squiggles
        if (!empty($replacements)) {
            $_values = [];
            foreach ($replacements as $_key => $_value) {
                if (!is_array($_value) && !is_object($_value)) {
                    $_key = '{' . $_key . '}';
                    $_values[$_key] = $_value;
                }
            }

            $_setting = str_replace(array_keys($_values), array_values($_values), $_setting);
        }

        if ($_stringified) {
            $_setting = JsonFile::decode($_setting);
        }

        return $_setting;
    }

    /**
     * @param Filesystem $filesystem
     * @param string     $source
     * @param string     $destination
     *
     * @return bool
     */
    protected function _writeStream($filesystem, $source, $destination)
    {
        if (false !== ($_fd = fopen($source, 'r'))) {
            //  Fallback gracefully if no stream support
            if (method_exists($filesystem, 'writeStream')) {
                $_result = $filesystem->writeStream($destination, $_fd, []);
            } elseif (method_exists($filesystem->getAdapter(), 'writeStream')) {
                $_result = $filesystem->getAdapter()->writeStream($destination, $_fd, $filesystem->getConfig());
            } else {
                $_result = $filesystem->put($destination, file_get_contents($source));
            }

            fclose($_fd);

            return $_result;
        }

        return false;
    }

}