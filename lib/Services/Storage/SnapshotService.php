<?php
namespace DreamFactory\Enterprise\Services\Storage;

use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Facades\Scalpel;
use DreamFactory\Enterprise\Services\Enums\Provisioners;
use DreamFactory\Enterprise\Services\InstanceValidation;
use DreamFactory\Library\Fabric\Common\Utility\Json;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\Inflector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

/**
 * Snapshot services
 */
class SnapshotService
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

    use InstanceValidation;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Creates a snapshot of a fabric-hosted instance
     *
     * @param string     $instanceId
     * @param Filesystem $fsSource
     * @param Filesystem $fsDestination
     * @param int        $keepDays The number of days to keep the snapshot
     *
     * @return array
     */
    public function create( $instanceId, Filesystem $fsSource, Filesystem $fsDestination, $keepDays = 30 )
    {
        //  Build our "mise en place", as it were...
        $_stamp = date( 'YmdHis' );
        $_instance = $this->_validateInstance( $instanceId );
        $_instanceName = $_instance->instance_name_text;
        $_tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dreamfactory' . DIRECTORY_SEPARATOR . 'tmp.dfe';

        //  A-Z, 0-9, and inner dashes (i.e. "abc-xyz"), but not outer (i.e., "-abc-")
        $_idPrefix = trim(
                preg_replace(
                    '/[^A-Za-z0-9-]+/',
                    null,
                    Config::get( 'services.snapshot.id-prefix', static::SNAPSHOT_ID_PREFIX )
                ),
                ' -'
            ) . '-';

        $_id = implode( '.', [Inflector::neutralize( $_instanceName ), $_idPrefix . $_stamp] );

        //  Start building our metadata array
        $_metadata = [
            'id'                         => $_id,
            'type'                       => Config::get( 'services.snapshot.metadata-type', 'dfe.snapshot' ),
            'source_cluster_id'          => (int)$_instance->cluster_id,
            'source_instance_id'         => $_instance->instance_id_text,
            'source_database_id'         => $_instance->dbServer->server_id_text,
            'source_storage_key'         => $_instance->storage_id_text,
            'source_private_key'         => $_instance->user->storage_id_text,
            'snapshot_prefix'            => $_id,
            'contents_storage_timestamp' => (int)time(),
            'contents_db_timestamp'      => (int)time(),
        ];

        $_zipFileName = Scalpel::make( 'services.snapshot.templates.snapshot-file-name', $_metadata );
        $_metadata['contents_storage_zipball'] = $_storageZipName = Scalpel::make( 'services.snapshot.templates.storage-file-name', $_metadata );
        $_sqlFileName = Scalpel::make( 'services.snapshot.templates.db-file-name', $_metadata );

        $_metadata['contents_db_zipball'] = $_sqlFileName . '.gz';

        $_metadata['hash'] = $_hash = RouteHashing::create( $_zipFileName, $keepDays );
        $_metadata['link'] = rtrim(
                Config::get( 'services.snapshot.hash_link_base' ),
                ' /'
            ) . '/' . $_hash;

        //  Prep the temp space...
        if ( !is_dir( $_tempPath ) && !@mkdir( $_tempPath, 0777, true ) && !@chmod( $_tempPath, 0777 ) )
        {
            throw new \RuntimeException( 'Cannot create temporary work space "' . $_tempPath . '". Aborting.' );
        }

        //  Mount file systems
        $_fsSnapshot = new Filesystem( new ZipArchiveAdapter( $_tempPath . DIRECTORY_SEPARATOR . $_zipFileName ) );
        $_fsStorage = new Filesystem( new ZipArchiveAdapter( $_tempPath . DIRECTORY_SEPARATOR . $_storageZipName ) );

        //  Archive storage
        if ( !$this->_archivePath( $fsSource, $_fsStorage ) )
        {
            throw new \RuntimeException( 'Unable to archive source file system. Aborting.' );
        }

        //  Unset to close file
        unset( $_fsStorage );

        //  Stuff it in the snapshot
        $this->_moveWorkFileToArchive( $_fsSnapshot, $_tempPath . DIRECTORY_SEPARATOR . $_storageZipName );

        //  Pull a database backup...
        if ( !$this->_dumpDatabase( $_instance, $_tempPath . '/' . $_sqlFileName, $_fsSnapshot ) )
        {
            throw new \RuntimeException( 'Unable to dump source database. Aborting.' );
        }

        $_md = Scalpel::make( 'services.snapshot.templates.metadata', $_metadata );

        //  Put it in the snapshot...
        $_fsSnapshot->put( 'snapshot.json', Json::encode( $_md ) );

        //  Unset to close file
        unset( $_fsSnapshot );

        //  Stuff it in the snapshot
        $this->_moveWorkFileToArchive( $fsDestination, $_tempPath . DIRECTORY_SEPARATOR . $_zipFileName );

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
        //        $_workPath = $_tempPath . '/' . $_workFile;
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
        //            Log::error( 'Error extracting snapshot tarball: ' . $_ex->getMessage() );
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
        //        $_command = 'cd ' . $this->getStoragePath() . '; rm -rf ./*; /bin/tar zxf ' . $_tempPath . '/' . $_snapshot->storage->tarball . ' ./';
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
     * @param FilesystemInterface $source The source file system to archive
     * @param FilesystemInterface $zip    The full zip file name
     *
     * @return bool
     */
    protected function _archivePath( $source, $zip )
    {
        try
        {
            foreach ( $source->listContents( '', true ) as $_file )
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
                    $zip->putStream( $_file['path'], $source->readStream( $_file['path'] ) );
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
            switch ( $instance->guest_location_nbr )
            {
                case Provisioners::DREAMFACTORY_ENTERPRISE:
                    //  This script automatically gzips the resultant file...
                    $_command =
                        sprintf(
                            "sudo -u %s %s %s %s",
                            Config::get( 'services.snapshot.script.user', 'dfadmin' ),
                            Config::get( 'services.snapshot.script.location' ),
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
                Log::warning( 'Failed to remove work file "' . $dumpFile . '" after database dump.' );
            }

            return true;
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * @param Filesystem $archive
     * @param string     $workFile
     */
    protected function _moveWorkFileToArchive( $archive, $workFile )
    {
        if ( false === ( $_stream = fopen( $workFile, 'rb' ) ) )
        {
            throw new \InvalidArgumentException( 'Unable to read file "' . $workFile . '".' );
        }

        $archive->putStream( basename( $workFile ), $_stream );

        if ( fclose( $_stream ) )
        {
            unlink( $workFile );
        }

        return;
    }

}