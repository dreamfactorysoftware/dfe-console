<?php
namespace DreamFactory\Enterprise\Common\Containers;

use DreamFactory\Enterprise\Common\Contracts\InstanceContainer;
use DreamFactory\Enterprise\Common\Contracts\InstanceFactory;
use DreamFactory\Enterprise\Common\Contracts\ProvisionerContract;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Enums\ServerTypes;
use DreamFactory\Enterprise\Services\Exceptions\DuplicateInstanceException;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Enterprise\Services\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Traits\InstanceValidation;
use DreamFactory\Library\Fabric\Common\Utility\Json;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Utility\FileSystem;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * A basic container for a RAVE instance
 */
class RaveBox implements InstanceContainer, InstanceFactory
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, InstanceValidation;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Instance
     */
    protected $_instance;
    /**
     * @type ProvisionerContract
     */
    protected $_provisioner;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new instance record
     *
     * @param string $instanceName
     * @param array  $options Array of options for creation
     *
     * @return array
     * @throws \DreamFactory\Enterprise\Services\Exceptions\DuplicateInstanceException
     */
    public function make( $instanceName, $options = [] )
    {
        if ( false === ( $_sanitized = Instance::isNameAvailable( $instanceName ) ) )
        {
            throw new DuplicateInstanceException( 'The instance name "' . $instanceName . '" is not available.' );
        }

        try
        {
            //  Basic checks...
            if ( null === ( $_ownerId = IfSet::get( $options, 'owner-id' ) ) )
            {
                throw new \InvalidArgumentException( 'No "owner-id" given. Cannot create instance.' );
            }

            try
            {
                $_owner = $this->_findUser( $_ownerId );
            }
            catch ( \Exception $_ex )
            {
                throw new \InvalidArgumentException( 'The "owner-id" specified is invalid.' );
            }

            $_clusterId = IfSet::get( $options, 'cluster-id', config( 'dfe.provisioning.default-cluster-id' ) );

            try
            {
                $_cluster = $this->_findCluster( $_clusterId );
                $_servers = $this->_clusterServers( $_cluster->id );
            }
            catch ( ModelNotFoundException $_ex )
            {
                throw new \RuntimeException( 'The specified cluster "' . $_clusterId . '" is not value.' );
            }

            //  Find the database server
            $_dbServer = null;

            if ( !empty( $_servers ) )
            {
                /** @type Server $_server */
                foreach ( $_servers[ServerTypes::DB] as $_server )
                {
                    $_dbServer = $_server;
                    break;
                }
            }

            //  Misconfigured cluster?
            if ( empty( $_dbServer ) )
            {
                throw new \RuntimeException( 'No database server is configured for cluster "' . $_cluster->cluster_id_text . '".' );
            }

            //  Where is this going?
            $_guestLocation = IfSet::get( $options, 'guest-location', config( 'dfe.provisioning.default-guest-location' ) );

            /** @type Instance $_model */
            $_model = new static();
            $_model->user_id = $_owner->id;
            $_model->cluster_id = $_cluster->id;
            $_model->db_server_id = $_dbServer->id;
            $_model->vendor_id = $_guestLocation;
            $_model->vendor_image_id = config( 'dfe.provisioning.default-vendor-image-id' );
            $_model->vendor_credentials_id = 0; //	DreamFactory account
            $_model->platform_state_nbr = 0; // Not Activated
            $_model->ready_state_nbr = 0; // Admin Required
            $_model->state_nbr = ProvisionStates::CREATED;
            $_model->flavor_nbr = config( 'dfe.provisioning.default-vendor-image-flavor' );
            $_model->trial_instance_ind = IfSet::get( $options, 'trial', false ) ? 1 : 0;
            $_model->guest_location_nbr = $_guestLocation;
            $_model->instance_name_text = $_sanitized;

            if ( !$_model->save() )
            {
                throw new \RuntimeException( 'Failed to save instance to database.' );
            }

            return $_model;
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Instance creation error: ' . $_ex->getMessage() );
        }
    }

    /**
     * Retrieves an instances' metadata
     *
     * @param Instance|int|string $instanceId
     *
     * @return array
     */
    public function getInstanceMetadata( $instanceId = null )
    {
        $_instance =
            null === $instanceId
                ? $this->_instance
                : ( ( $instanceId instanceof Instance ) ? $instanceId : $this->_validateInstance( $instanceId ) );

        if ( !$_instance || !( $_instance instanceof Instance ) )
        {
            throw new \InvalidArgumentException( 'The $instanceId provided was not found or invalid.' );
        }

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

    //******************************************************************************
    //* Methods
    //******************************************************************************

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
        $_instance = $request->get( 'instance' );
        $_dbName = str_replace( '-', '_', $_instance->instance_name_text );
        $_storageKey = $_instance->storage_id_text;
        $_storage = $request->getStorage();
        $_storagePath = $request->getStoragePath();
        $_privatePath = $request->getPrivatePath();
        $_relativePrivatePath = str_replace( $_storagePath, null, $_privatePath );
        $_dbConfigFile = $_relativePrivatePath . DIRECTORY_SEPARATOR . $_instance->instance_name_text . '.database.config.php';
        $_instanceMetadata = $_relativePrivatePath . DIRECTORY_SEPARATOR . $_instance->instance_name_text . '.json';

        //	Make sure the user name is kosher
        list( $_dbUser, $_dbPassword ) = $this->_generateDbUser( $_instance->instance_name_text );

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

        $_host =
            $_instance->instance_name_text .
            '.' .
            config( 'dfe.provisioning.default-dns-zone' ) .
            '.' .
            config( 'dfe.provisioning.default-dns-domain' );

        //	Update instance with new provision info
        try
        {
            $this->_instance->fill(
                [
                    'guest_location_nbr' => GuestLocations::DFE_CLUSTER,
                    'instance_id_text'   => $_instance->instance_name_text,
                    'instance_name_text' => $_instance->instance_name_text,
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

            $this->_instance->save();
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Exception while storing new instance data: ' . $_ex->getMessage() );
        }

        $_md = [];

        try
        {
            $_md = $this->getInstanceMetadata( $_instance );

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
            'cluster'             => $this->_instance->cluster_id,
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
     * @param \DreamFactory\Library\Fabric\Database\Models\Deploy\Instance $instance
     *
     * @return $this
     */
    public function setInstance( Instance $instance )
    {
        $this->_instance = $instance;

        return $this;
    }

    /**
     * @return Instance
     */
    public function getInstance()
    {
        return $this->_instance;
    }

}