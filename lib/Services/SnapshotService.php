<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Support\SnapshotManifest;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Utility\Inflector;
use DreamFactory\Library\Utility\JsonFile;
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

    use EntityLookup, Archivist;

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
        $_workPath = $this->getWorkPath($_snapshotId, true) . DIRECTORY_SEPARATOR;

        //  Create the snapshot archive and stuff it full of goodies
        $_fsSnapshot = new Filesystem(new ZipArchiveAdapter($_workPath . $_metadata['name']));

        //  Grab the provisioner
        $_services = Provision::getPortableServices($_instance->guest_location_nbr);

        try {
            foreach ($_services as $_type => $_service) {
                $_to = $_workPath . $_snapshotId . '.' . $_type;
                $_request = new ProvisioningRequest($_instance);

                if (false !== ($_outfile = $_service->export($_request, $_to))) {
                    $this->moveWorkFile($_fsSnapshot, $_workPath . $_outfile);
                }
            }
        } catch (\Exception $_ex) {
            //  Delete the temp path...
            $_fsSnapshot = null;
            $this->deleteWorkPath($_snapshotId);

            return false;
        }

        //  Create a snapshot manifesto
        $_manifest = new SnapshotManifest($_metadata, config('snapshot.metadata-file-name'), $_fsSnapshot);
        $_manifest->write();

        //  Close up the files
        /** @noinspection PhpUndefinedMethodInspection */
        $_fsSnapshot->getAdapter()->getArchive()->close();
        $_fsSnapshot = null;

        //  Move the snapshot archive into the "snapshots" private storage area
        $this->moveWorkFile($destination ?: $_instance->getSnapshotMount(), $_workPath . $_metadata['name']);

        //  Cleanup
        $this->deleteWorkPath($_snapshotId);

        return true;
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
}