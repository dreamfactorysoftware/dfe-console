<?php
namespace Cerberus\Services\Hosting\Instance;

use Cerberus\Enums\DSP;
use Cerberus\Enums\Provisioners;
use Cerberus\Services\Hosting\BaseHostingService;
use Cerberus\Services\Provisioning\DreamFactory;
use Cerberus\Yii\Models\Deploy\RouteHash;
use DreamFactory\Common\Exceptions\RestException;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\Curl;
use Kisma\Core\Utility\Log;

/**
 * Snapshot
 * Creates an instance snapshot
 *
 * @author        Jerry Ablan <jerryablan@dreamfactory.com>
 */
class Snapshot extends BaseHostingService
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var string
     */
    const DOWNLOAD_URL_BASE = 'http://cerberus.fabric.dreamfactory.com/api/download';

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Retrieves info about the latest snapshot
     *
     * @param string $instanceId
     *
     * @return array
     */
    public function latest( $instanceId )
    {
        $_files = $this->all( $instanceId );

        if ( !empty( $_files ) )
        {
            return array(key( $_files ) => current( $_files ));
        }

        return array();
    }

    /**
     * Retrieves info about the latest snapshot
     *
     * @param string $instanceId
     *
     * @return array
     */
    public function all( $instanceId )
    {
        $_instance = $this->_validateInstance( $instanceId );

        $_result = array();

        foreach ( glob( $_instance->getSnapshotPath() . static::SNAPSHOT_GLOB ) as $_file )
        {
            //                                              0.             1.       2.  3. 4
            //	Split up the snapshot file name (instanceName.[yymmddhhmmss].snapshot.tar.gz)
            $_parts = explode( '.', basename( $_file ) );

            //	Not a snapshot file...
            if ( count( $_parts ) != 5 )
            {
                Log::debug( 'File not a snapshot: ' . $_file );
                continue;
            }

            if ( !isset( $_result[$_parts[0]] ) )
            {
                $_result[$_parts[0]] = array();
            }

            $_result[$_parts[0]][] = array(
                'snapshot_id' => $_parts[0] . '.' . $_parts[1],
                'date'        => date( 'c', strtotime( $_parts[1] ) ),
                'file'        => basename( $_file ),
                'path'        => dirname( $_file )
            );
        }

        Log::debug( 'Snapshots available: ' . print_r( $_result, true ) );

        return $_result;
    }

    /**
     * Downloads a snapshot file
     *
     * @param string|int $instanceId
     * @param string     $snapshotId
     * @param bool       $returnFileNameOnly
     *
     * @return string
     * @throws RestException
     */
    public function download( $instanceId, $snapshotId, $returnFileNameOnly = false )
    {
        $_instance = $this->_validateInstance( $instanceId );
        $_snapshot = $_instance->getSnapshotPath() . '/' . $snapshotId . static::SNAPSHOT_FILE_SUFFIX;

        if ( !file_exists( $_snapshot ) )
        {
            throw new RestException(
                HttpResponse::NotFound,
                'Snapshot "' . $snapshotId . '" not found. (' . $_snapshot . ')'
            );
        }

        if ( false !== $returnFileNameOnly )
        {
            return $_snapshot;
        }

        header( 'Content-type: application/octet-stream' );
        header( 'Content-disposition: attachment; filename=' . basename( $_snapshot ) );
        readfile( $_snapshot );

        Pii::end();
    }

    /**
     * @param string $instanceId
     * @param string $snapshotId
     *
     * @throws \DreamFactory\Common\Exceptions\RestException
     * @return bool
     */
    public function delete( $instanceId, $snapshotId )
    {
        $this->_validateInstance( $instanceId );
        $_snapshot = $this->_snapshotPathFromId( $snapshotId );

        if ( !file_exists( $_snapshot ) )
        {
            throw new RestException( HttpResponse::NotFound );
        }

        return ( 0 == `rm $_snapshot; echo $?` );
    }

    /**
     * Creates a snapshot of a fabric-hosted instance
     *
     * @param string $instanceId
     * @param bool   $finalBackup If true, the snapshot is saved off to the global snapshot area with the DSP name prepended.
     * @param int    $keepDays    The number of days to keep the snapshot
     *
     * @return array|bool
     */
    public function create( $instanceId, $finalBackup = false, $keepDays = 30 )
    {
        $_instance = $this->_validateInstance( $instanceId );

        //	Make a goody bag
        try
        {
            $_tempPath = $this->_makeTempDirectory();
        }
        catch ( \RuntimeException $_ex )
        {
            //	Problem making directory, ignore...
            $_tempPath = '/tmp';
            Log::notice( 'Could not make tmp dir, using /tmp' );
        }

        //	Get the file name settled
        $_stamp = date( 'YmdHis' );
        $_snapshotFile = $_instance->instance_name_text . '.' . $_stamp . static::SNAPSHOT_FILE_SUFFIX;
        $_storageFile = $_instance->instance_name_text . '.' . $_stamp . static::STORAGE_FILE_SUFFIX;
        $_dumpFile = $_instance->instance_name_text . '.' . $_stamp . static::MYSQLDUMP_FILE_SUFFIX;

        //.........................................................................
        //. 1.	Bundle up the storage area
        //.........................................................................

        //	If we're local, the command is a bit different...
        if ( Provisioners::DreamFactory == $this->_instance->guest_location_nbr )
        {
            $_fileTree = Curl::get( 'http://' . $_instance->instance_name_text . '.cloud.dreamfactory.com/web/fileTree' );

            $_command = 'cd ' . $this->getStoragePath() . '; ' .
                'tar zcf ' . $_tempPath . '/' . $_storageFile . ' ./; ' .
                'chmod 777 ' . $_tempPath . '/' . $_storageFile;
        }
        else
        {
            $_fileTree
                = '/usr/bin/ssh ' . static::DEFAULT_SSH_OPTIONS . ' dfadmin@' . $this->_instance->instance_name_text . DSP::DEFAULT_DSP_SUB_DOMAIN .
                ' \'php /var/www/launchpad/current/config/storage.helper.php\'';

            exec( $_fileTree, $_output, $_return );
            $_fileTree = json_decode( implode( PHP_EOL, $_output ) );
            $_output = $_return = null;

            $_command
                = '/usr/bin/ssh ' . static::DEFAULT_SSH_OPTIONS . ' dfadmin@' . $this->_instance->instance_name_text . DSP::DEFAULT_DSP_SUB_DOMAIN .
                ' \'cd /var/www/launchpad/current/storage/; tar zcf - .\'>' . $_tempPath . '/' . $_storageFile .
                '; chmod 777 ' . $_tempPath . '/' . $_storageFile;
        }

        $_result = exec( $_command, $_output, $_return );

        if ( 0 != $_return )
        {
            $this->logError(
                'Error pulling storage directory of dsp "' . $instanceId . '": ' . ( false === $_result
                    ? 0
                    :
                    $_result ) . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL
            );

            $this->logError( 'OUTPUT: ' . PHP_EOL . implode( PHP_EOL, $_output ) );

            return false;
        }

        $this->logDebug( 'Storage pulled: ' . $_storageFile );

        //.........................................................................
        //. 2. Dump MySQL
        //.........................................................................

        if ( Provisioners::DreamFactory == $this->_instance->guest_location_nbr )
        {
            $_command
                =
                'sudo -u dfadmin /opt/dreamfactory/fabric/cerberus/config/scripts/snapshot_mysql.sh ' .
                $this->_instance->db_name_text .
                ' ' .
                $_tempPath .
                '/' .
                $_dumpFile;
        }
        else
        {
            $_command
                = '/usr/bin/ssh ' . static::DEFAULT_SSH_OPTIONS . ' dfadmin@' . $this->_instance->instance_name_text . DSP::DEFAULT_DSP_SUB_DOMAIN .
                ' \'mysqldump --delayed-insert -e -u ' . $this->_instance->db_user_text . ' -p' . $this->_instance->db_password_text . ' ' .
                $this->_instance->db_name_text . '\' | gzip -c >' . $_tempPath . '/' . $_dumpFile;
        }

        $_result = exec( $_command, $_output, $_return );

        if ( 0 != $_return )
        {
            $this->logError(
                'Error pulling mysql dump of dsp "' . $instanceId . '": ' . $_result . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL
            );
            $this->logError( implode( PHP_EOL, $_output ) );

            return false;
        }

        $this->logDebug( 'MySQL dump pulled: ' . $_dumpFile );

        //.........................................................................
        //. 3.	Build metadata
        //.........................................................................

        $_snapshot = array(
            'snapshot_id' => $_stamp,
            'storage_key' => $_instance->storage_id_text,
            'imports'     => array(),
            'storage'     => array(
                'tarball'  => $_storageFile,
                'contents' => $_fileTree,
            ),
            'mysql'       => array(
                'tarball' => $_dumpFile,
            ),
        );

        file_put_contents( $_tempPath . '/snapshot.json', json_encode( $_snapshot ) );

        //.........................................................................
        //. 4.	Wrap it up into a nice little package and store
        //.........................................................................

        //  Make sure we have the permissions squared away...
        exec(
            dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/config/scripts/fix-perms.sh ' . escapeshellarg(
                $_snapshotPath = $_instance->getSnapshotPath()
            ) . ' -d'
        );

        $_snapshotRoot = $_snapshotPath;
        $_snapshotPath .= '/' . $_snapshotFile;

        $_command =
            'cd ' . $_tempPath . '; ' .
            '@mkdir ' . escapeshellarg( $_snapshotRoot ) . '>/dev/null 2>&1; ' .
            'tar --remove-files -zcf ' . $_snapshotPath . ' ./*';

        $_result = exec( $_command, $_output, $_return );

        if ( 0 != $_return )
        {
            $this->logError(
                'Error archiving storage directory of dsp "' .
                $instanceId .
                '": ' .
                $_result .
                ' (' .
                $_return .
                ')' .
                PHP_EOL .
                $_command .
                PHP_EOL .
                implode( PHP_EOL, $_output )
            );

            return false;
        }

        //	Get an exploding hash
        $_hash = RouteHash::hashFileForDownload( $_snapshotPath );
        $_snapshot['download_link'] = static::DOWNLOAD_URL_BASE . '/' . $_hash;
        $_snapshot['snapshot_hash'] = $_hash;
        $_snapshot['file_path'] = $_snapshotPath;

        //	Return the metadata
        return $_snapshot;
    }

    /**
     * Given an instance and a snapshot ID, replace the data with that of the snapshot.
     *
     * @param string|int $instanceId
     * @param string     $snapshot
     *
     * @throws \DreamFactory\Common\Exceptions\RestException
     * @return bool
     */
    public function restore( $instanceId, $snapshot )
    {
        $_instance = $this->_validateInstance( $instanceId );

        //	1. Grab the tarball...
        $_tempPath = $this->_makeTempDirectory( $_workFile );
        $_workPath = $_tempPath . '/' . $_workFile;

        file_put_contents( $_workPath, file_get_contents( $this->download( $instanceId, $snapshot, true ) ) );

        //	2. Crack it open and get the goodies
        $_import = new \PharData( $_workPath );

        try
        {
            $_import->extractTo( dirname( $_workPath ) );

            if ( false === ( $_snapshot = json_decode( file_get_contents( $_tempPath . '/snapshot.json' ) ) ) )
            {
                throw new RestException( HttpResponse::BadRequest, 'Invalid snapshot "' . $snapshot . '"' );
            }
        }
        catch ( \Exception $_ex )
        {
            $this->logError( 'Error extracting snapshot tarball: ' . $_ex->getMessage() );

            $this->_killTempDirectory( $_tempPath );

            return false;
        }

        //	2. Make a snapshot of the existing data first (backup)
        $_backup = static::create( $instanceId, true );

        //	3. Install snapshot storage files
        $_command = 'cd ' . $this->getStoragePath() . '; rm -rf ./*; /bin/tar zxf ' . $_tempPath . '/' . $_snapshot->storage->tarball . ' ./';
        $_result = exec( $_command, $_output, $_return );

        if ( 0 != $_return )
        {
            $this->logError(
                'Error importing storage directory of dsp "' . $instanceId . '": ' . $_result . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL
            );
            $this->logError( implode( PHP_EOL, $_output ) );
            $this->_killTempDirectory( $_tempPath );

            return false;
        }

        //	4. Drop old, Create new database for snapshot mysql data
        $_db = Pii::db( 'db.cumulus' );

        if ( Provisioners::DreamFactory == $this->_instance->guest_location_nbr )
        {
            $_command
                = 'sudo -u dfadmin /opt/dreamfactory/fabric/cerberus/config/scripts/restore_snapshot_mysql.sh ' .
                $this->_instance->db_name_text .
                ' ' .
                $_workPath;
        }
        else
        {
//			$_command
//				= '/usr/bin/ssh ' . static::DEFAULT_SSH_OPTIONS . ' dfadmin@' . $this->_instance->instance_name_text . DSP::DEFAULT_DSP_SUB_DOMAIN .
//				' \'mysqldump --delayed-insert -e -u ' . $this->_instance->db_user_text . ' -p' . $this->_instance->db_password_text . ' ' .
//				$this->_instance->db_name_text . '\' | gzip -c >' . $_workPath;
        }

        $_result = exec( $_command, $_output, $_return );

        if ( 0 != $_return )
        {
            $this->logError(
                'Error restoring mysql dump of dsp "' . $instanceId . '": ' . $_result . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL
            );
            $this->logError( implode( PHP_EOL, $_output ) );
            $this->_killTempDirectory( $_tempPath );

            //@TODO need to restore snapshot taken at the beginning cuz we sucked at this...

            return false;
        }

        $this->logDebug( 'MySQL dump restored: ' . $_workFile );
//
//		//	5. Import mysql data
//
//		$_command
//			= 'cd ' . $_tempPath . '; ' .
//			'gunzip ' . $_snapshot->mysql->tarball . '; ' .
//			'mysql -u ' . $_db->username . ' -p' . $_db->password . ' -h ' . DSP::DEFAULT_DSP_SERVER . ' --database=' .
//			$this->_instance->db_name_text . ' < mysql.' . $snapshot . '.sql';
//
//		$_result = exec( $_command, $_output, $_return );
//
//		if ( 0 != $_return )
//		{
//			$this->logError( 'Error importing mysql dump of dsp "' . $instanceId . '": ' . $_result . ' (' . $_return . ')' . PHP_EOL . $_command . PHP_EOL );
//			$this->logError( implode( PHP_EOL, $_output ) );
//
//			//	Roll everything back...
//			$_service->deprovision(
//				array(
//					 'name'        => $this->_instance->instance_name_text,
//					 'storage_key' => $this->_instance->storage_id_text
//				),
//				true,
//				$this->_instance
//			);
//
//			$this->_killTempDirectory( $_tempPath );
//
//			return false;
//		}
//
//		//	6.	Update snapshot with import info
//		$_snapshot->imports[] = array(
//			'timestamp' => date( 'c' ),
//		);
//
//		$_import->addFromString( 'snapshot.json', json_encode( $_snapshot ) );

        //	7. Cleanup
        $this->_killTempDirectory( $_tempPath );

        //	Import complete!!!
        return true;
    }
}