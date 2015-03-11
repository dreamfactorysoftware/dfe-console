<?php
namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Common\Contracts\InstanceProvisioner;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Fabric\Common\Utility\Json;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\FileSystem;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Support\Facades\Log;

/**
 * An instance manipulation Wrapper
 */
class RemoteInstance extends Instance implements InstanceProvisioner
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const CHARACTER_PATTERN = '/[^a-zA-Z0-9]/';
    /**
     * @type string
     */
    const HOST_NAME_PATTERN = "/^([a-zA-Z0-9])+$/";

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Update the operational state of this instance
     *
     * @param int $state
     *
     * @return bool|int
     */
    public function updateState( $state )
    {
        $this->state_nbr = $state;

        return $this->update( ['state_nbr' => $state] );
    }

    /**
     * Given a provisioning request with a storage area, provision a new instance
     *
     * @param ProvisioningRequest $request
     *
     * @return array|bool
     * @throws ProvisioningException
     */
    public function up( ProvisioningRequest $request )
    {
        //	Clean up that nasty name...
        $_instance = $request->getInstance();

        if ( null === ( $_name = $this->_sanitizeName( $_instance->instance_name_text ) ) )
        {
            throw new ProvisioningException( 'You must specify the "name" of the new instance.' );
        }

        $_dbName = str_replace( '-', '_', $_name );
        $_storageKey = $_instance->storage_id_text;

        $_storage = $request->getStorage();
        $_storagePath = $request->get( 'storage-path' );
        $_privatePath = $request->get( 'private-path' );
        $_relativePrivatePath = str_replace( $_storagePath, null, $_privatePath );
        $_dbConfigFile = $_relativePrivatePath . DIRECTORY_SEPARATOR . $_name . '.database.config.php';
        $_instanceMetadata = $_relativePrivatePath . DIRECTORY_SEPARATOR . $_name . '.json';

        //	Make sure the user name is kosher
        list( $_dbUser, $_dbPassword ) = $this->_generateDbUser( $_name );

        try
        {
            $_dbConfig = $this->_getDatabaseConfig( $_dbName );

            //	1. Create database
            if ( !$this->_createDatabase( $_dbConfig['server'], $_dbName ) )
            {
                \Log::error( 'Unable to create database "' . $_dbName . '"' );

                return false;
            }

            //	2. Grant privileges
            if ( !$this->_grantPrivileges( $_dbConfig['server'], $_dbName, $_dbUser, $_dbPassword ) )
            {
                try
                {
                    //	Try and get rid of the database we created
                    $this->_dropDatabase( $_dbConfig['server'], $_dbName );
                }
                catch ( \Exception $_ex )
                {
                    \Log::error( 'Exception dropping database: ' . $_ex->getMessage() );
                }

                return false;
            }
        }
        catch ( \Exception $_ex )
        {
            throw new ProvisioningException( $_ex->getMessage(), $_ex->getCode() );
        }

        //	3. Create files in storage
        try
        {
            //	Create database config file...
            $_date = date( 'c' );

            $_php = <<<PHP
<?php
/**
 * **** DO NOT MODIFY THIS FILE ****
 * **** CHANGES WILL BREAK YOUR INSTANCE AND MAY BE OVERWRITTEN AT ANY TIME ****
 * @(#)\$Id: database.config.php; v2.0.0-{$_dbName} {$_date} \$
 */
return array(
	'connectionString'      => 'mysql:host={$_dbConfig['host']};port={$_dbConfig['port']};dbname={$_dbName}',
	'username'              => '{$_dbUser}',
	'password'              => '{$_dbPassword}',
	'emulatePrepare'        => true,
	'charset'               => 'utf8',
	'schemaCachingDuration' => 3600,
);
PHP;

            if ( !$_storage->put( $_dbConfigFile, $_php ) )
            {
                \Log::error( 'Error writing database configuration file: ' . $_dbConfigFile );

                return false;
            }
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'Exception prepping storage: ' . $_ex->getMessage() );
            $this->_dropDatabase( $_dbConfig['server'], $_dbName );
            FileSystem::rmdir( $_storagePath, true );

            return false;
        }

        $_host = $_name . '.' . config( 'dfe.provisioning.default-dns-zone' ) . '.' . config( 'dfe.provisioning.default-dns-domain' );

        //	Update instance with new provision info
        try
        {
            $this->fill(
                [
                    'guest_location_nbr' => GuestLocations::DFE_CLUSTER,
                    'instance_id_text'   => $_name,
                    'instance_name_text' => $_name,
                    'db_host_text'       => $_dbConfig['host'],
                    'db_port_nbr'        => $_dbConfig['port'],
                    'db_name_text'       => $_dbName,
                    'db_user_text'       => $_dbUser,
                    'db_password_text'   => $_dbPassword,
                    'base_image_text'    => 'fabric.standard',
                    'public_host_text'   => $_host,
                    'ready_state_nbr'    => 0, //   Admin Required
                    'state_nbr'          => ProvisionStates::PROVISIONED,
                    'platform_state_nbr' => 0, //   Not Activated
                    'vendor_state_nbr'   => ProvisionStates::PROVISIONED,
                    'vendor_state_text'  => 'running',
                    'start_date'         => date( 'c' ),
                    'end_date'           => null,
                    'terminate_date'     => null,
                    'provision_ind'      => 1,
                    'deprovision_ind'    => 0,
                    'cluster_id'         => $_instance->cluster_id,
                ]
            );

            $this->save();
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Exception while storing new instance data: ' . $_ex->getMessage() );
        }

        $_md = [];

        try
        {
            $_md = $this->getMetadata( $_instance );

            if ( !$_storage->put( $_instanceMetadata, Json::encode( $_md ) ) )
            {
                \Log::error( 'Error writing instance metadata file: ' . $_dbConfigFile );
            }
        }
        catch ( \Exception $_ex )
        {
            //  Don't stop for me...
        }

        //  Fire off a "launch" event...
        \Event::fire( 'dfe.launch', [$this, $_md] );

        return [
            'host'                => $_host,
            'storage_key'         => $_storageKey,
            'blob_path'           => $_storagePath,
            'storage_path'        => $_storagePath,
            'private_path'        => $_privatePath,
            'snapshot_path'       => $_privatePath . DIRECTORY_SEPARATOR . 'snapshots',
            'db_host'             => $_dbConfig['host'],
            'db_port'             => $_dbConfig['port'],
            'db_name'             => $_dbName,
            'db_user'             => $_dbUser,
            'db_password'         => $_dbPassword,
            'db_config_file_name' => $_dbConfigFile,
            'cluster'             => $this->cluster_id,
            'metadata'            => $_md,
        ];
    }

    /**
     * Destroys an instance
     *
     * @param ProvisioningRequest $request
     *
     * @return mixed
     */
    public function down( ProvisioningRequest $request )
    {
        $_instance = $request->getInstance();

        //  Snapshot
        //$this->_export($instanceId,$options=[])

        //  Drop database...
        //$this->_dropDatabase()

        //  Drop storage
        //$this->_deprovisionStorage($request)

        //	Update the current instance state
        $_instance->provision_ind = 0;
        $_instance->deprovision_ind = 1;
        $_instance->terminate_date = $_instance->end_date = date( 'c' );
        $_instance->state_nbr = ProvisionStates::DEPROVISIONED;

        return $_instance->save();
    }

    /**
     * Replaces an instance with an existing snapshot
     *
     * @param ProvisioningRequest $request
     *
     * @return mixed
     */
    public function replace( ProvisioningRequest $request )
    {
    }

    /**
     * Performs a complete wipe of an instance. It is not destroyed, but the database is completely wiped and recreated as if this were brand new.
     * Files in /storage are NOT touched.
     *
     * @param ProvisioningRequest $request
     *
     * @return mixed
     */
    public function wipe( ProvisioningRequest $request )
    {
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function _sanitizeName( $name )
    {
        static $_unavailableNames = [];

        //	This replaces any disallowed characters with dashes
        $_clean = str_replace(
            [' ', '_'],
            '-',
            trim( str_replace( '--', '-', preg_replace( static::CHARACTER_PATTERN, '-', $name ) ), ' -_' )
        );

        if ( null === $_unavailableNames )
        {
            /** @noinspection PhpIncludeInspection */
            $_unavailableNames = @include( config_path() . DIRECTORY_SEPARATOR . 'unavailable_names.config.php' );

            if ( !is_array( $_unavailableNames ) || empty( $_unavailableNames ) )
            {
                $_unavailableNames = [];
            }
        }

        //	Check host name
        if ( preg_match( static::HOST_NAME_PATTERN, $_clean ) )
        {
            Log::notice( 'Non-standard instance name "' . $_clean . '" being provisioned' );
        }

        if ( in_array( $_clean, $_unavailableNames ) )
        {
            Log::error( 'Attempt to register banned instance name: ' . $name . ' => ' . $_clean );

            throw new \InvalidArgumentException( 'The name "' . $name . '" is not available.' );
        }

        return $_clean;
    }

    /**
     * Generates a unique user/pass for MySQL
     *
     * @param string $name
     *
     * @return array|string
     */
    protected function _generateDbUser( $name )
    {
        $_dbUser = $_dbPassword = null;

        //  Make sure our user name is unique...
        while ( true )
        {
            $_baseHash = sha1( microtime( true ) . $name . env( 'APP_KEY' ) );
            $_dbUser = substr( 'u' . $_baseHash, 0, 16 );

            if ( 0 == Instance::where( 'db_user_text', '=', $_dbUser )->count() )
            {
                break;
            }

            usleep( 500000 );
        }

        $_dbPassword = sha1( microtime( true ) . $name . $_dbUser . microtime( true ) );

        return [$_dbUser, $_dbPassword];
    }

    /**
     * @param string $dbName
     *
     * @return array
     */
    protected function _getDatabaseConfig( $dbName )
    {
        $_dbServer = $_dbHost = $_dbPort = $_dbConfig = null;

        if ( $this->dbServer )
        {
            $_dbServer = $this->dbServer->server_id_text;

            if ( !empty( $_dbServer ) )
            {
                $_dbConfig = $this->dbServer->config_text;

                if ( empty( $_dbConfig ) )
                {
                    throw new \LogicException( 'No configuration found for database.' );
                }
                else if ( is_string( $_dbConfig ) )
                {
                    $_dbConfig = Json::decode( $_dbConfig );
                }

                $_dbHost = IfSet::get( $_dbConfig, 'host' );
                $_dbPort = IfSet::get( $_dbConfig, 'port' );
            }
        }

        if ( empty( $_dbServer ) )
        {
            $_dbServer = 'db-legacy';
            $_dbHost = 'localhost';
            $_dbPort = 3306;
        }

        Log::debug( 'Using db-server-id "' . $_dbServer . '"' );

        $_dbConfig = array_merge(
            [
                'driver'    => IfSet::get( $_dbConfig, 'driver', 'mysql' ),
                'host'      => $_dbHost,
                'port'      => $_dbPort,
                'database'  => $dbName ?: IfSet::get( $_dbConfig, 'database' ),
                'username'  => IfSet::get( $_dbConfig, 'username' ),
                'password'  => IfSet::get( $_dbConfig, 'password' ),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'server'    => $_dbServer,
            ],
            $_dbConfig
        );

        //  Stuff a config in the system for our db
        \Config::set( 'database.connections.' . $_dbServer, $_dbConfig );

        return $_dbConfig;
    }

    /**
     * @param string $db
     * @param string $name
     *
     * @return bool
     */
    protected function _createDatabase( $db, $name )
    {
        try
        {
            return \DB::connection( $db )->statement(
                <<<MYSQL
CREATE DATABASE IF NOT EXISTS `{$name}`
MYSQL
            );
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * @param string $db
     * @param string $name
     *
     * @return bool
     */
    protected function _dropDatabase( $db, $name )
    {
        $name = '`' . str_replace( '-', '_', $name ) . '`';

        try
        {
            return \DB::connection( $db )->statement(
                <<<MYSQL
SET FOREIGN_KEY_CHECKS = 0; DROP DATABASE {$name};
MYSQL
            );
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * @param string $db
     * @param string $name
     * @param string $user
     * @param string $password
     *
     * @return bool
     */
    protected function _grantPrivileges( $db, $name, $user, $password )
    {
        //  Create user
        \DB::connection( $db )->statement( 'CREATE USER \'' . $user . '\'@\'%\' IDENTIFIED BY \'' . $password . '\'' );

        $_sql = <<<MYSQL
GRANT ALL PRIVILEGES ON {$name}.* TO '{$user}'@'%'
MYSQL;

        try
        {
            //	Grants for instance database
            return \DB::connection( $db )->statement( $_sql );
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * Retrieves an instances' metadata
     *
     * @param Instance|int|string $instanceId
     *
     * @return array
     */
    public function getMetadata( $instanceId )
    {
        $_instance = ( $instanceId instanceof Instance ) ? $instanceId : $this->_validateInstance( $instanceId );

        if ( !$_instance->user )
        {
            throw new \RuntimeException( 'The user for instance "' . $_instance->instance_id_text . '" was not found.' );
        }

        $_response = [
            'instance-id'         => $_instance->id,
            'cluster-id'          => $_instance->cluster_id,
            'db-server-id'        => $_instance->db_server_id,
            'app-server-id'       => $_instance->app_server_id,
            'web-server-id'       => $_instance->web_server_id,
            'owner-id'            => $_instance->user_id,
            'owner-email-address' => $_instance->user->email_addr_text,
        ];

        return $_response;
    }
}