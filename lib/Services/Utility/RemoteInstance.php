<?php
namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Services\Contracts\Instance\Control;
use DreamFactory\Enterprise\Services\Enums\Provisioners;
use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Requests\ProvisioningRequest;
use DreamFactory\Enterprise\Services\Traits\InstanceValidation;
use DreamFactory\Library\Fabric\Common\Utility\Json;
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Utility\FileSystem;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * An instance manipulation class
 *
 * @property integer            $user_id
 * @property integer            $cluster_id
 * @property integer            $vendor_id
 * @property integer            $vendor_image_id
 * @property integer            $vendor_credentials_id
 * @property integer            $guest_location_nbr
 * @property string             $instance_id_text
 * @property int                $app_server_id
 * @property int                $db_server_id
 * @property int                $web_server_id
 * @property string             $db_host_text
 * @property int                $db_port_nbr
 * @property string             $db_name_text
 * @property string             $db_user_text
 * @property string             $db_password_text
 * @property string             $storage_id_text
 * @property integer            $flavor_nbr
 * @property string             $base_image_text
 * @property string             $instance_name_text
 * @property string             $region_text
 * @property string             $availability_zone_text
 * @property string             $security_group_text
 * @property string             $ssh_key_text
 * @property integer            $root_device_type_nbr
 * @property string             $public_host_text
 * @property string             $public_ip_text
 * @property string             $private_host_text
 * @property string             $private_ip_text
 * @property string             $request_id_text
 * @property string             $request_date
 * @property integer            $deprovision_ind
 * @property integer            $provision_ind
 * @property integer            $trial_instance_ind
 * @property integer            $state_nbr
 * @property integer            $platform_state_nbr
 * @property integer            $ready_state_nbr
 * @property integer            $vendor_state_nbr
 * @property string             $vendor_state_text
 * @property integer            $environment_id
 * @property integer            $activate_ind
 * @property string             $start_date
 * @property string             $end_date
 * @property string             $terminate_date
 *
 * Relations:
 *
 * @property User               $user
 * @property Server             $appServer
 * @property Server             $dbServer
 * @property Server             $webServer
 *
 * @method static Builder instanceName( string $instanceName )
 * @method static Builder byNameOrId( string $instanceNameOrId )
 * @method static Builder withDbName( string $dbName )
 * @method static Builder onDbServer( int $dbServerId )
 */
class RemoteInstance implements Control
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
    //* Members
    //******************************************************************************

    /**
     * @type Instance
     */
    protected $_instance;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param Instance $instance
     */
    public function __construct( Instance $instance )
    {
        $this->_instance = $instance;
    }

    /**
     * Update the operational state of this instance
     *
     * @param int $state
     *
     * @return bool|int
     */
    public function updateState( $state )
    {
        $this->_instance->state_nbr = $state;

        return $this->_instance->update( ['state_nbr' => $state] );
    }

    /**
     * Creates a snapshot of a fabric-hosted instance
     *
     * @param ProvisioningRequest $request
     *
     * @return array|bool
     * @throws ProvisioningException
     */
    public function launch( ProvisioningRequest $request )
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
        $_storagePath = $request->getStoragePath();
        $_privatePath = $request->getPrivatePath();
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
                Log::error( 'Unable to create database "' . $_dbName . '"' );

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
                    Log::error( 'Exception dropping database: ' . $_ex->getMessage() );
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
                Log::error( 'Error writing database configuration file: ' . $_dbConfigFile );

                return false;
            }
        }
        catch ( \Exception $_ex )
        {
            Log::error( 'Exception prepping storage: ' . $_ex->getMessage() );
            $this->_dropDatabase( $_dbConfig['server'], $_dbName );
            FileSystem::rmdir( $_storagePath );

            return false;
        }

        $_host = $_name . '.' . config( 'dfe.provisioning.default-dns-zone' ) . '.' . config( 'dfe.provisioning.default-dns-domain' );

        //	Update instance with new provision info
        try
        {
            $this->fill(
                [
                    'guest_location_nbr' => Provisioners::DREAMFACTORY_ENTERPRISE,
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
                    'cluster_id'         => $_dbConfig['cluster'],
                ],
                false
            );

            $this->save();

            $_md = $this->getMetadata( $_instance );

            if ( !$_storage->put( $_instanceMetadata, Json::encode( $_md ) ) )
            {
                Log::error( 'Error writing instance metadata file: ' . $_dbConfigFile );
            }
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Exception while storing new instance data: ' . $_ex->getMessage() );
        }

        //  Fire off a "launch" event...
        Event::fire( 'dfe.launch', [$this, $_md] );

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
            'metadata'            => $_md,
        ];
    }

    /**
     * Destroys a DSP
     *
     * @param ProvisioningRequest $request
     *
     * @return mixed
     */
    public function destroy( ProvisioningRequest $request )
    {
    }

    /**
     * Replaces a DSP with an existing snapshot
     *
     * @param ProvisioningRequest $request
     *
     * @return mixed
     */
    public function replace( ProvisioningRequest $request )
    {
    }

    /**
     * Performs a complete wipe of a DSP. The DSP is not destroyed, but the database is completely wiped and recreated as if this were a brand new
     * DSP. Files in the storage area are NOT touched.
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
            Log::notice( 'Non-standard DSP name "' . $_clean . '" being provisioned' );
        }

        if ( in_array( $_clean, $_unavailableNames ) )
        {
            Log::error( 'Attempt to register banned DSP name: ' . $name . ' => ' . $_clean );

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
     * @return array
     */
    protected function _getDatabaseConfig( $dbName )
    {
        $_dbServer = $_dbHost = $_dbPort = $_dbConfig = null;
        $_clusterId = $this->cluster_id;

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
                'cluster'   => $_clusterId,
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
            //	Grants for DSP database
            return \DB::connection( $db )->statement( $_sql );
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * @param string $name
     * @param bool   $deleteRecord
     * @param string $originalHost
     * @param string $zone
     * @param string $domain
     * @param string $recordType
     * @param int    $ttl
     * @param string $comment
     *
     * @return bool
     */
    protected function _addRemoveDnsEntry( $name, $deleteRecord = false, $originalHost = null, $zone = null, $domain = null, $recordType = 'CNAME', $ttl = 3600, $comment = null )
    {
        $_zone = $zone ?: (string)config( 'dfe.provisioning.default-dns-zone' );
        $_domain = $domain ?: (string)config( 'dfe.provisioning.default-dns-domain' );

        return rtrim( $name, '. ' ) . '.' . $_zone . '.' . $_domain;
    }

    /**
     * Pass any unrecognized calls through to the instance record
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call( $name, $arguments )
    {
        return call_user_func_array( [$this->_instance, $name], $arguments );
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function __set( $name, $value )
    {
        $this->_instance->{$name} = $value;

        return $this;
    }

    public function __get( $name )
    {
        return $this->_instance->{$name};
    }

    /**
     * @return Instance
     */
    public function getInstance()
    {
        return $this->_instance;
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
            throw new \RuntimeException( 'The user for instance "' . $instanceId . '" was not found.' );
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