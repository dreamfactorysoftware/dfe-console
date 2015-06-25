<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Exceptions\NotImplementedException;
use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Support\SnapshotManifest;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Facades\InstanceStorage;
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
        try {
            //  Build our "mise en place", as it were...
            $_stamp = date('YmdHis');
            $_instance = $this->_findInstance($instanceId);
            $_instanceName = $_instance->instance_name_text;
            $_source = $_instance->getStorageMount();

            //  Make our temp path...
            $_workPath = $this->_getTempFilesystem($_instance->id . '_' . $_stamp, true);

            //  Snapshot id & name
            $_snapshotId = $_stamp . '.' . Inflector::neutralize($_instanceName);

            //  Start building our snapshot metadata array
            $_metadata = [
                'id'              => $_snapshotId,
                'type'            => config('snapshot.metadata-type', 'application/json'),
                'snapshot-prefix' => $_snapshotId,
                'timestamp'       => $_stamp,
            ];

            $_metadata['name'] =
                $this->_getConfigValue('snapshot.templates.snapshot-file-name', $_metadata);

            $_metadata['storage-export'] =
                $this->_getConfigValue('snapshot.templates.storage-file-name', $_metadata);

            $_metadata['database-export'] =
                $this->_getConfigValue('snapshot.templates.db-file-name', $_metadata);

            $_metadata['hash'] = RouteHashing::create($_metadata['name'], $keepDays);
            $_metadata['link'] = rtrim(config('snapshot.hash_link_base'), ' /') . '/' . $_metadata['hash'];

            //  Archive storage
            if (!$this->_archivePath($_source, $_workPath . DIRECTORY_SEPARATOR . $_metadata['storage-export'])) {
                throw new \RuntimeException('Unable to archive source file system. Aborting.');
            }

            //  Mount snapshot and stuff the new files in it
            $_fsSnapshot = new Filesystem(new ZipArchiveAdapter($_workPath . DIRECTORY_SEPARATOR . $_metadata['name']));
            $this->_moveWorkFileToArchive($_fsSnapshot,
                $_workPath . DIRECTORY_SEPARATOR . $_metadata['storage-export']);

            //  Pull a database backup...
            if (!$this->_dumpDatabase($_instance,
                $_workPath . DIRECTORY_SEPARATOR . $_metadata['database-export'],
                $_fsSnapshot)
            ) {
                throw new \RuntimeException('Unable to dump source database. Aborting.');
            }

            $_md = $this->_getConfigValue('snapshot.templates.metadata', $_metadata);

            //  Put it in the snapshot...
            $_manifest = new SnapshotManifest($_fsSnapshot, array_merge($_md, $_metadata));
            $_manifest->write();

            //  Unset to close file(s)
            unset($_fsSnapshot);

            //  Stuff it in the snapshot
            $this->_moveWorkFileToArchive(
                $destination ?: InstanceStorage::getSnapshotMount($_instance),
                $_workPath . DIRECTORY_SEPARATOR . $_metadata['name']
            );

            return $_md;
        } catch (\Exception $_ex) {
            \Log::error('Exception thrown during snapshot: ' . $_ex->getMessage());
            throw $_ex;
        }
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
     * @return bool
     */
    protected function _archivePath($source, $zipPath)
    {
        $zip = new Filesystem(new ZipArchiveAdapter($zipPath));

        try {
            foreach ($source->listContents('', true) as $_file) {
                if ($_file['type'] == 'dir') {
                    $zip->createDir($_file['path']);
                } elseif ($_file['type'] == 'link') {
                    $zip->put($_file['path'], $_file['target']);
                } elseif ($_file['type'] == 'file') {
                    file_exists($_file['path']) && $this->_writeStream($zip, $_file['path'], $_file['path']);
                }
            }

            //  Flush zip to disk
            $zip = null;

            return true;
        } catch (\Exception $_ex) {
            return false;
        }
    }

    /**
     * @param Instance   $instance
     * @param string     $dumpFile
     * @param Filesystem $zip
     *
     * @return bool
     * @throws NotImplementedException
     */
    protected function _dumpDatabase($instance, $dumpFile, $zip)
    {
        if ($instance->guest_location_nbr != GuestLocations::DFE_CLUSTER) {
            throw new NotImplementedException();
        }

        try {
            //  This script automatically gzips the resultant file...
            $_command = str_replace(PHP_EOL, null, `which mysqldump`);
            $_template = $_command . ' --compress --delayed-insert {options} >' . $dumpFile;
            $_port = $instance->db_port_nbr;
            $_name = $instance->db_name_text;

            $_options = [
                '--host=' . escapeshellarg($instance->db_host_text),
                '--user=' . escapeshellarg($instance->db_user_text),
                '--password=' . escapeshellarg($instance->db_password_text),
                '--databases ' . escapeshellarg($_name),
            ];

            if (!empty($_port)) {
                $_options[] = '--port=' . $_port;
            }

            $_command = str_replace('{options}', implode(' ', $_options), $_template);
            $_result = exec($_command, $_output, $_return);

            if (0 != $_return) {
                throw new \RuntimeException('Error while dumping database of instance id "' .
                    $instance->instance_id_text .
                    '".');
            }

            $this->_writeStream($zip, $dumpFile, basename($dumpFile));

            //  Clear up some junk
            unset($_command, $_result, $_output, $_return);

            //  Try and delete...
            if (false === @unlink($dumpFile)) {
                $this->warning('Failed to remove work file "' . $dumpFile . '" after database dump.');
            }

            return true;
        } catch (\Exception $_ex) {
            return false;
        }
    }

    /**
     * @param Filesystem|\Illuminate\Contracts\Filesystem\Filesystem $archive
     * @param string                                                 $workFile
     */
    protected function _moveWorkFileToArchive($archive, $workFile = null)
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