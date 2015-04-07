<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Library\Fabric\Common\Utility\Json;
use DreamFactory\Library\Fabric\Database\Enums\GuestLocations;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\Inflector;
use DreamFactory\Library\Utility\JsonFile;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
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
    const SNAPSHOT_FILE_SUFFIX = '.snapshot.zip';
    /**
     * @type string
     */
    const STORAGE_FILE_SUFFIX = '.storage.zip';
    /**
     * @type string
     */
    const SQL_FILE_SUFFIX = '.sql';
    /**
     * @type string
     */
    const SQL_ZIP_SUFFIX = '.sql.gz';
    /**
     * @type string The snapshot ID prefix
     */
    const SNAPSHOT_ID_PREFIX = 'ess';

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
     * @param string                                      $instanceId
     * @param \Illuminate\Contracts\Filesystem\Filesystem $fsDestination
     * @param int                                         $keepDays The number of days to keep the snapshot
     *
     * @return array
     */
    public function create( $instanceId, \Illuminate\Contracts\Filesystem\Filesystem $fsDestination = null, $keepDays = 30 )
    {
        //  Build our "mise en place", as it were...
        $_stamp = date( 'YmdHis' );
        $_instance = $this->_findInstance( $instanceId );
        $_instanceName = $_instance->instance_name_text;
        $_fsSource = $_instance->getStorageMount();

        //  Make our temp path...
        $_tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dfe' . DIRECTORY_SEPARATOR . 'tmp';
        !is_dir( $_tempPath ) && mkdir( $_tempPath, 0777, true );

        //  A-Z, 0-9, and inner dashes (i.e. "abc-xyz"), but not outer (i.e., "-abc-")
        $_idPrefix = trim(
                preg_replace(
                    '/[^A-Za-z0-9-]+/',
                    null,
                    config( 'snapshot.id-prefix', static::SNAPSHOT_ID_PREFIX )
                ),
                ' -'
            ) . '-';

        $_id = implode( '.', [Inflector::neutralize( $_instanceName ), $_idPrefix . $_stamp] );

        //  Start building our metadata array
        $_metadata = array_merge(
            [
                'id'                         => $_id,
                'type'                       => config( 'snapshot.metadata-type', 'dfe.snapshot' ),
                'snapshot-prefix'            => $_id,
                'contents-storage-timestamp' => (int)time(),
                'contents-db-timestamp'      => (int)time(),
            ],
            $_instance->getMetadata()
        );

        $_zipFileName = $this->_getConfigValue( 'snapshot.templates.snapshot-file-name', $_metadata );
        $_metadata['contents-storage-zipball'] = $_storageZipName =
            $this->_getConfigValue( 'snapshot.templates.storage-file-name', $_metadata );
        $_sqlFileName = $this->_getConfigValue( 'snapshot.templates.db-file-name', $_metadata );
        $_metadata['contents-db-zipball'] = $_sqlFileName . '.gz';
        $_metadata['hash'] = $_hash = RouteHashing::create( $_zipFileName, $keepDays );
        $_metadata['link'] = rtrim(
                config( 'snapshot.hash_link_base' ),
                ' ' . DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR . $_hash;

        //  Prep the temp space...
        if ( !is_dir( $_tempPath ) && !@mkdir( $_tempPath, 0777, true ) && !@chmod( $_tempPath, 0777 ) )
        {
            throw new \RuntimeException( 'Cannot create temporary work space "' . $_tempPath . '". Aborting.' );
        }

        //  Mount storage file system and archive
        $_fsStorage = new Filesystem( new ZipArchiveAdapter( $_tempPath . DIRECTORY_SEPARATOR . $_storageZipName ) );

        //  Archive storage
        if ( !$this->_archivePath( $_fsSource, $_fsStorage ) )
        {
            throw new \RuntimeException( 'Unable to archive source file system. Aborting.' );
        }

        //  Unset to close file
        unset( $_fsStorage );

        //  Mount snapshot and stuff the new files in it
        $_fsSnapshot = new Filesystem( new ZipArchiveAdapter( $_tempPath . DIRECTORY_SEPARATOR . $_zipFileName ) );
        $this->_moveWorkFileToArchive( $_fsSnapshot, $_tempPath . DIRECTORY_SEPARATOR . $_storageZipName );

        //  Pull a database backup...
        if ( !$this->_dumpDatabase( $_instance, $_tempPath . DIRECTORY_SEPARATOR . $_sqlFileName, $_fsSnapshot ) )
        {
            throw new \RuntimeException( 'Unable to dump source database. Aborting.' );
        }

        $_md = $this->_getConfigValue( 'snapshot.templates.metadata', $_metadata );

        //  Put it in the snapshot...
        $_fsSnapshot->put( 'snapshot.json', Json::encode( $_md ) );

        //  Unset to close file
        unset( $_fsSnapshot );

        //  Stuff it in the snapshot
        $this->_moveWorkFileToArchive( $fsDestination ?: $_instance->getSnapshotMount(), $_tempPath . DIRECTORY_SEPARATOR . $_zipFileName );

        return $_md;
    }

    /**
     * Given an instance and a snapshot ID, replace the data with that of the snapshot.
     *
     * @param string|int $instanceId
     * @param string     $snapshot
     *
     * @return bool
     */
    public function restore( $instanceId, $snapshot )
    {
        //        $_instance = $this->_validateInstance( $instanceId );
        //
        //        //	1. Grab the tarball...
        //        $_tempPath = $this->_getTempFilesystem( $_workFile );
        //        $_workPath = $_tempPath . DIRECTORY_SEPARATOR . $_workFile;
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
        //            if ( false === ( $_snapshot = json_decode( file_get_contents( $_tempPath . '/snapshot.json' ) ) ) )
        //            {
        //                throw new RestException( HttpResponse::BadRequest, 'Invalid snapshot "' . $snapshot . '"' );
        //            }
        //        }
        //        catch ( \Exception $_ex )
        //        {
        //            $this->error( 'Error extracting snapshot tarball: ' . $_ex->getMessage() );
        //
        //            $this->_killTempDirectory( $_tempPath );
        //
        //            return false;
        //        }
        //
        //        //	2. Make a snapshot of the existing data first (backup)
        //        $_backup = static::create( $instanceId, true );
        //
        //        //	3. Install snapshot storage files
        //        $_command = 'cd ' . $this->getStoragePath() . '; rm -rf ./*; /bin/tar zxf ' . $_tempPath . DIRECTORY_SEPARATOR . $_snapshot->storage->tarball . ' ./';
        //        $_result = exec( $_command, $_output, $_return );
        //
        //        if ( 0 != $_return )
        //        {
        //            Log::error(
        //                'Error importing storage directory of dsp "' . $instanceId . '": ' . $_result . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL
        //            );
        //            Log::error( implode( PHP_EOL, $_output ) );
        //            $this->_killTempDirectory( $_tempPath );
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
        //            $this->_killTempDirectory( $_tempPath );
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
        ////			= 'cd ' . $_tempPath . '; ' .
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
        ////			$this->_killTempDirectory( $_tempPath );
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
        //        $this->_killTempDirectory( $_tempPath );
        //
        //        //	Import complete!!!
        //        return true;
    }

    /**
     * @param string $base Optional path in which to create the temporary directory
     *
     * @return Filesystem
     */
    protected function _getTempFilesystem( $base = null )
    {
        //  Set our temp base
        return new Filesystem( new Local( $base ?: sys_get_temp_dir() ) );
    }

    /**
     * @param FilesystemAdapter   $source The source file system to archive
     * @param FilesystemInterface $zip    The full zip file name
     *
     * @return bool
     */
    protected function _archivePath( $source, $zip )
    {
        /** @type AdapterInterface $_adapter */
        /** @noinspection PhpUndefinedMethodInspection */
        $_adapter = $source->getDriver()->getAdapter();

        try
        {
            foreach ( $_adapter->listContents( '', true ) as $_file )
            {
                if ( $_file['type'] == 'dir' )
                {
                    $zip->createDir( $_file['path'] );
                }
                elseif ( $_file['type'] == 'link' )
                {
                    $zip->put( $_file['path'], $_file['target'] );
                }
                elseif ( $_file['type'] == 'file' )
                {
                    $_resource = $_adapter->readStream( $_file['path'] );
                    $zip->putStream( $_file['path'], $_resource['stream'] );
                }
            }

            return true;
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * @param Instance            $instance
     * @param string              $dumpFile
     * @param FilesystemInterface $zip
     *
     * @return bool
     */
    protected function _dumpDatabase( $instance, $dumpFile, $zip )
    {
        try
        {
            $_script = config( 'snapshot.script.location' );

            switch ( $instance->guest_location_nbr )
            {
                case GuestLocations::DFE_CLUSTER:
                    $_script = config( 'snapshot.script.location' );
                    if ( !file_exists( $_script ) )
                    {
                        throw new \RuntimeException( 'No snapshot script configured.' );
                    }

                    if ( !is_executable( $_script ) )
                    {
                        if ( false === chmod( $_script, 0750 ) )
                        {
                            throw new \RuntimeException( 'Script found, but not executable.' );
                        }
                    }

                    //  This script automatically gzips the resultant file...
                    $_dbServer = $this->_findServer( $instance->db_server_id );
                    $_args = [
                        '--db-host=' . '"' . $_dbServer->config_text['host'] . '"',
                        '--db-user=' . '"' . $_dbServer->config_text['username'] . '"',
                        '--db-pass=' . '"' . $_dbServer->config_text['password'] . '"',
                        '--db-port=' . $_dbServer->config_text['port'],
                    ];

                    $_command =
                        sprintf(
                            "sudo -u %s %s %s %s",
                            config( 'snapshot.script.user', 'dfadmin' ),
                            $_script . ' ' . implode( ' ', $_args ),
                            $instance->db_name_text,
                            $dumpFile
                        );

                    $_result = exec( $_command, $_output, $_return );

                    if ( 0 != $_return )
                    {
                        throw new \RuntimeException( 'Error while dumping database of instance id "' . $instance->instance_id_text . '".' );
                    }

                    $dumpFile .= '.gz';

                    //  Add dump to zip and delete from temp
                    //@todo chunk or stream this copy so huge database files don't run out of memory
                    $zip->put( basename( $dumpFile ), file_get_contents( $dumpFile ) );

                    //  Clear up some junk
                    unset( $_command, $_result, $_output, $_return );
                    break;

                default:
                    throw new \RuntimeException( 'This feature is not available for the source instance\'s guest location.' );
            }

            //  Try and delete...
            if ( false === @unlink( $dumpFile ) )
            {
                $this->warning( 'Failed to remove work file "' . $dumpFile . '" after database dump.' );
            }

            return true;
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * @param Filesystem|\Illuminate\Contracts\Filesystem\Filesystem $archive
     * @param string                                                 $workFile
     */
    protected function _moveWorkFileToArchive( $archive = null, $workFile = null )
    {
        if ( false === ( $_stream = fopen( $workFile, 'rb' ) ) )
        {
            throw new \InvalidArgumentException( 'Unable to read file "' . $workFile . '".' );
        }

        if ( empty( $archive ) )
        {

        }

        $archive->putStream( basename( $workFile ), $_stream );

        if ( fclose( $_stream ) )
        {
            unlink( $workFile );
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
    protected function _getConfigValue( $key, $replacements )
    {
        $_stringified = false;
        $_setting = config( $key );

        if ( !is_string( $_setting ) )
        {
            if ( false === JsonFile::encode( $_setting ) )
            {
                throw new \InvalidArgumentException( 'The value at key "' . $key . '" is not a string or a jsonable array.' );
            }

            $_stringified = true;
        }

        //  Surround keys with squiggles
        if ( !empty( $replacements ) )
        {
            $_values = [];
            foreach ( $replacements as $_key => $_value )
            {
                if ( is_string( $_value ) )
                {
                    $_key = '{' . $_key . '}';
                    $_values[$_key] = $_value;
                }
            }

            $_setting = str_replace( array_keys( $_values ), array_values( $_values ), $_setting );
        }

        if ( $_stringified )
        {
            $_setting = JsonFile::decode( $_setting );
        }

        return $_setting;
    }

}