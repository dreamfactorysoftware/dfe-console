<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Facades\RouteHashing;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Support\SnapshotManifest;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Database\Models\RouteHash;
use DreamFactory\Enterprise\Database\Models\Snapshot;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Utility\Inflector;
use DreamFactory\Library\Utility\Json;
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

    use EntityLookup, Archivist, Notifier;

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
        $_instance = $this->_findInstance($instanceId);
        $_instanceName = $_instance->instance_name_text;

        //  Create the snapshot ID
        $_snapshotId = $_stamp . '.' . Inflector::neutralize($_instanceName);

        //  Start building our metadata array
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

        //  Build our link hash
        $_metadata['name'] = $this->_getConfigValue('snapshot.templates.snapshot-file-name', $_metadata);
        $_metadata['hash'] = RouteHashing::create($_metadata['name'], $keepDays);
        $_metadata['link'] = '//' . str_replace(['http://', 'https://', '//'],
                null,
                rtrim(config('snapshot.hash-link-base'), ' /')) . '/' . $_metadata['hash'];

        //  Make our temp path...
        $_workPath = $this->getWorkPath($_snapshotId, true) . DIRECTORY_SEPARATOR;

        //  Create the snapshot archive and stuff it full of goodies
        $_fsSnapshot = new Filesystem(new ZipArchiveAdapter($_workPath . $_metadata['name']));

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
                    $this->moveWorkFile($_fsSnapshot, $_workPath . $_outfile);
                }
            }

            try {
                //  Create our snapshot manifesto
                $_manifest = new SnapshotManifest($_metadata, config('snapshot.metadata-file-name'), $_fsSnapshot);
                $_manifest->write();

                //  Close up the files
                /** @noinspection PhpUndefinedMethodInspection */
                $this->flushZipArchive($_fsSnapshot);

                //  Move the snapshot archive into the "snapshots" private storage area
                $this->moveWorkFile($destination ?: $_instance->getSnapshotMount(), $_workPath . $_metadata['name']);

                //  Generate a record for the dashboard
                $_routeHash = RouteHash::byHash($_metadata['hash'])->first();

                //  Create our snapshot record
                Snapshot::create([
                    'user_id'          => $_instance->user_id,
                    'instance_id'      => $_instance->id,
                    'route_hash_id'    => $_routeHash->id,
                    'snapshot_id_text' => $_snapshotId,
                    'public_ind'       => true,
                    'public_url_text'  => $_metadata['link'],
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
<a href="{$_metadata['link']}">{$_metadata['link']}</a> from any browser.</p>
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
            $this->deleteWorkPath($_snapshotId);
        }

        return $_success;
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
            if (false === ($_setting = Json::encode($_setting))) {
                throw new \InvalidArgumentException('The value at key "' . $key . '" is not a string or a jsonable array.');
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
            $_setting = Json::decode($_setting);
        }

        return $_setting;
    }
}