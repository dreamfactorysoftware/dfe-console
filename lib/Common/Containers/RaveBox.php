<?php
namespace DreamFactory\Enterprise\Common\Containers;

use DreamFactory\Enterprise\Common\Contracts\InstanceContainer;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Contracts\StaticInstanceFactory;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Common\Traits\StaticComponentLookup;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Providers\ProvisioningServiceProvider;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

/**
 * A basic container for a RAVE instance
 *
 * CODE WAS TRANSITIONAL. DO NOT USE. See InstanceManager.php
 */
class RaveBox implements InstanceContainer, StaticInstanceFactory
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const DEFAULT_CACHE_PREFIX = 'dfe.rave-box';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use StaticComponentLookup, InstanceValidation;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Instance
     */
    protected $_instance;
    /**
     * @type ResourceProvisioner
     */
    protected $_provisioner;

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
    public function up( $request )
    {
        return \App::make( ProvisioningServiceProvider::IOC_NAME )->provision( $request );
//
//        //	Clean up that nasty name...
//        $_instance = $request->getInstance();
//        $_dbName = str_replace( '-', '_', $_instance->instance_name_text );
//        $_storageKey = $_instance->storage_id_text;
//
//        $_storage = $request->getStorage();
//        $_storagePath = $_storage->getStoragePath();
//        $_privatePath = $_storage->getPrivateInstancePath();
//
//        $_relativePrivatePath = str_replace( $_storagePath, null, $_privatePath );
//        $_dbConfigFile = $_relativePrivatePath . DIRECTORY_SEPARATOR . $_instance->instance_name_text . '.database.config.php';
//        $_instanceMetadata = $_relativePrivatePath . DIRECTORY_SEPARATOR . $_instance->instance_name_text . '.json';
//
//        //	Make sure the user name is kosher
//        list( $_dbUser, $_dbPassword ) = $this->_generateDbUser( $_instance->instance_name_text );
//
//        try
//        {
//            $_dbConfig = $this->_getDatabaseConfig( $_dbName );
//
//            //	1. Create database
//            if ( !$this->_createDatabase( $_dbConfig['server'], $_dbName ) )
//            {
//                Log::error( 'Unable to create database "' . $_dbName . '"' );
//
//                return false;
//            }
//
//            //	2. Grant privileges
//            if ( !$this->_grantPrivileges( $_dbConfig['server'], $_dbName, $_dbUser, $_dbPassword ) )
//            {
//                try
//                {
//                    //	Try and get rid of the database we created
//                    $this->_dropDatabase( $_dbConfig['server'], $_dbName );
//                }
//                catch ( \Exception $_ex )
//                {
//                    Log::error( 'Exception dropping database: ' . $_ex->getMessage() );
//                }
//
//                return false;
//            }
//        }
//        catch ( \Exception $_ex )
//        {
//            throw new ProvisioningException( $_ex->getMessage(), $_ex->getCode() );
//        }
//
//        //	3. Create files in storage
//        try
//        {
//            //	Create database config file...
//            $_date = date( 'c' );
//
//            $_php = <<<PHP
//<?php
///**
// * **** DO NOT MODIFY THIS FILE ****
// * **** CHANGES WILL BREAK YOUR INSTANCE AND MAY BE OVERWRITTEN AT ANY TIME ****
// * @(#)\$Id: database.config.php; v2.0.0-{$_dbName} {$_date} \$
// */
//return array(
//	'connectionString'      => 'mysql:host={$_dbConfig['host']};port={$_dbConfig['port']};dbname={$_dbName}',
//	'username'              => '{$_dbUser}',
//	'password'              => '{$_dbPassword}',
//	'emulatePrepare'        => true,
//	'charset'               => 'utf8',
//	'schemaCachingDuration' => 3600,
//);
//PHP;
//
//            if ( !$_storage->put( $_dbConfigFile, $_php ) )
//            {
//                \Log::error( 'Error writing database configuration file: ' . $_dbConfigFile );
//
//                return false;
//            }
//        }
//        catch ( \Exception $_ex )
//        {
//            \Log::error( 'Exception prepping storage: ' . $_ex->getMessage() );
//            $this->_dropDatabase( $_dbConfig['server'], $_dbName );
//            FileSystem::rmdir( $_storagePath, true );
//
//            return false;
//        }
//
//        $_host =
//            $_instance->instance_name_text .
//            '.' .
//            config( 'dfe.provisioning.default-dns-zone' ) .
//            '.' .
//            config( 'dfe.provisioning.default-dns-domain' );
//
//        //	Update instance with new provision info
//        try
//        {
//            $this->_instance->fill(
//                [
//                    'guest_location_nbr' => GuestLocations::DFE_CLUSTER,
//                    'instance_id_text'   => $_instance->instance_name_text,
//                    'instance_name_text' => $_instance->instance_name_text,
//                    'db_host_text'       => $_dbConfig['host'],
//                    'db_port_nbr'        => $_dbConfig['port'],
//                    'db_name_text'       => $_dbName,
//                    'db_user_text'       => $_dbUser,
//                    'db_password_text'   => $_dbPassword,
//                    'base_image_text'    => 'fabric.standard',
//                    'public_host_text'   => $_host,
//                    'ready_state_nbr'    => 0, //   Admin Required
//                    'state_nbr'          => ProvisionStates::PROVISIONED,
//                    'platform_state_nbr' => 0, //   Not Activated
//                    'vendor_state_nbr'   => ProvisionStates::PROVISIONED,
//                    'vendor_state_text'  => 'running',
//                    'start_date'         => date( 'c' ),
//                    'end_date'           => null,
//                    'terminate_date'     => null,
//                    'provision_ind'      => 1,
//                    'deprovision_ind'    => 0,
//                    'cluster_id'         => $_instance->cluster_id,
//                ]
//            );
//
//            $this->_instance->save();
//        }
//        catch ( \Exception $_ex )
//        {
//            throw new \RuntimeException( 'Exception while storing new instance data: ' . $_ex->getMessage() );
//        }
//
//        $_md = [];
//
//        try
//        {
//            $_md = $this->getInstanceMetadata( $_instance );
//
//            if ( !$_storage->put( $_instanceMetadata, Json::encode( $_md ) ) )
//            {
//                \Log::error( 'Error writing instance metadata file: ' . $_dbConfigFile );
//            }
//        }
//        catch ( \Exception $_ex )
//        {
//            //  Don't stop for me...
//        }
//
//        //  Fire off a "launch" event...
//        \Event::fire( 'dfe.launch', [$this, $_md] );
//
//        return [
//            'host'                => $_host,
//            'storage_key'         => $_storageKey,
//            'blob_path'           => $_storagePath,
//            'storage_path'        => $_storagePath,
//            'private_path'        => $_privatePath,
//            'snapshot_path'       => $_privatePath . DIRECTORY_SEPARATOR . 'snapshots',
//            'db_host'             => $_dbConfig['host'],
//            'db_port'             => $_dbConfig['port'],
//            'db_name'             => $_dbName,
//            'db_user'             => $_dbUser,
//            'db_password'         => $_dbPassword,
//            'db_config_file_name' => $_dbConfigFile,
//            'cluster'             => $this->_instance->cluster_id,
//            'metadata'            => $_md,
//        ];
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
        return \App::make( ProvisioningServiceProvider::IOC_NAME )->deprovision( $request );

//        //  Snapshot
//        //$this->_export($instanceId,$options=[])
//
//        //  Drop database...
//        //$this->_dropDatabase()
//
//        //  Drop storage
//        //$this->_deprovisionStorage($request)
//
//        //	Update the current instance state
//        $_instance->provision_ind = 0;
//        $_instance->deprovision_ind = 1;
//        $_instance->terminate_date = $_instance->end_date = date( 'c' );
//        $_instance->state_nbr = ProvisionStates::DEPROVISIONED;
//
//        return $_instance->save();
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