<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Providers\RaveDatabaseServiceProvider;
use DreamFactory\Enterprise\Services\RaveDatabaseService;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Contracts\Filesystem\Filesystem;

class RaveProvisioner extends BaseResourceProvisioner
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Get the current status of a provisioning request
     *
     * @param Instance $instance
     *
     * @return array
     */
    public function status( Instance $instance )
    {
        /** @var Instance $_instance */
        if ( null === ( $_instance = Instance::find( $instance->id ) ) )
        {
            return ['success' => false, 'error' => ['code' => 404, 'message' => 'Instance not found.']];
        }

        return ['success' => true, 'status' => $_instance->state_nbr, 'status_text' => ProvisionStates::prettyNameOf( $_instance->state_nbr )];
    }

    /**
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     * @param array                                                              $options
     *
     * @return array
     */
    protected function _doProvision( $request, $options = [] )
    {
        $_output = [];
        $_result = false;
        $_instance = $request->getInstance();

        //	Update the current instance state
        $_instance->updateState( ProvisionStates::PROVISIONING );

        try
        {
            //  Provision storage and fill in the request
            $this->_provisionStorage( $request, $options );

            //  And the instance
            $_result = $this->_provisionInstance( $request, $options );

            return ['success' => true, 'instance' => $_instance->toArray(), 'log' => $_output, 'result' => $_result];
        }
        catch ( \Exception $_ex )
        {
            $_instance->updateState( ProvisionStates::PROVISIONING_ERROR );

            //  Force-kill anything we made before blowing up
            $request->setForced( true );

            $this->_deprovisionStorage( $request );

            if ( !$this->_deprovisionInstance( $request ) )
            {
                $this->error( 'Unable to remove instance "' . $_instance->instance_id_text . '" after failed provision.' );
            }

            return ['success' => false, 'instance' => false, 'log' => $_output, 'result' => $_result];
        }
    }

    /**
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     * @param array                                                              $options
     *
     * @return array
     */
    protected function _doDeprovision( $request, $options = [] )
    {
        $_output = [];
        $_result = false;
        $_instance = $request->getInstance();

        //	Update the current instance state
        $_instance->updateState( ProvisionStates::DEPROVISIONING );

        try
        {
            $_result = $this->_deprovisionInstance( $request, $options );

            return ['success' => true, 'instance' => $_instance->toArray(), 'log' => $_output, 'result' => $_result];
        }
        catch ( \Exception $_ex )
        {
            $_instance->updateState( ProvisionStates::DEPROVISIONING_ERROR );

            return ['success' => false, 'instance' => false, 'log' => $_output, 'result' => $_result];
        }
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return Filesystem
     */
    protected function _provisionStorage( $request, $options = [] )
    {
        \Log::debug( '  * rave: provision storage - begin' );

        //  Use requested file system if one...
        if ( null === ( $_filesystem = $request->getStorage() ) )
        {
            /** @type Filesystem $_filesystem */
            $_filesystem = \Storage::disk( 'hosted' );
            $request->setStorage( $_filesystem );
        }

        //  Do it!
        $request->setStorageProvisioner( $_provisioner = Provision::resolveStorage( IfSet::get( $options, 'guest-location-nbr', 'rave' ) ) );
        $_provisioner->provision( $request );

        \Log::debug( '  * rave: provision storage - complete' );

        return $_filesystem;
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return bool
     */
    protected function _deprovisionStorage( $request, $options = [] )
    {
        \Log::debug( '  * rave: deprovision storage' );

        //  Use requested file system if one...
        if ( null === ( $_filesystem = $request->getStorage() ) )
        {
            /** @type Filesystem $_filesystem */
            $_filesystem = \Storage::disk( 'hosted' );
            $request->setStorage( $_filesystem );
        }

        //  Do it!
        Provision::resolveStorage( 'rave' )->deprovision( $request );

        \Log::debug( '  * rave: deprovision storage - complete' );

        return $_filesystem;
    }

    /**
     * @return array
     */
    protected function _getStorageConfig()
    {
        $_config = config( 'filesystems.disks.hosted' );

        if ( empty( $_config ) )
        {
            throw new \RuntimeException( 'No hosted storage configuration found.' );
        }

        return $_config;
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return array
     * @throws ProvisioningException
     */
    protected function _provisionInstance( $request, $options = [] )
    {
        \Log::debug( '  * rave: provision instance' );

        $_storagePath = null;

        //	Pull the request apart
        $_instance = $request->getInstance();
        $_name = $_instance->instance_name_text;
        $_storageKey = $_instance->storage_id_text;
        $_privatePath = $request->getStorageProvisioner()->getPrivatePath();
        $_ownerPrivatePath = $request->getStorageProvisioner()->getOwnerPrivatePath();
        $_dbConfigFile = $_privatePath . DIRECTORY_SEPARATOR . $_name . '.database.config.php';

        \Log::debug( '  * rave: provision instance > database' );

        //	1. Provision the database
        /** @type RaveDatabaseService $_dbService */
        $_dbService = \App::make( RaveDatabaseServiceProvider::IOC_NAME );

        if ( false === ( $_dbConfig = $_dbService->provision( $request ) ) )
        {
            throw new ProvisioningException( 'Failed to provision database. Check logs for error.' );
        }

        $_dbUser = $_dbConfig['username'];
        $_dbPassword = $_dbConfig['password'];
        $_dbName = $_dbConfig['database'];

        \Log::debug( '  * rave: provision instance > database - complete' );

        //  2. Update the instance...
        $_host = $_name . '.' . config( 'dfe.provisioning.default-dns-zone' ) . '.' . config( 'dfe.provisioning.default-dns-domain' );

        //	Update instance with new provision info
        try
        {
            $_instance->fill(
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

            $_instance->save();

            \Log::debug( '  * rave: provision instance > update - complete' );
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Error updating instance data: ' . $_ex->getMessage() );
        }

        //  Collect metadata
        $_md = array_merge(
            $_instance->getMetadata(),
            [
                'private-path'       => $_privatePath,
                'owner-private-path' => $_ownerPrivatePath,
                'storage-path'       => $_storagePath,
            ]
        );

        //  Fire off a "launch" event...
        \Log::debug( '  * rave: provision instance > fire event' );
        \Event::fire( 'dfe.launch', [$this, $request, $_md] );

        \Log::debug( '  * rave: provision instance - complete' );

        return [
            'host'                => $_host,
            'storage_key'         => $_storageKey,
            'blob_path'           => $_storagePath,
            'storage_path'        => $_storagePath,
            'private_path'        => $_privatePath,
            'owner_private_path'  => $_ownerPrivatePath,
            'snapshot_path'       => $_privatePath . DIRECTORY_SEPARATOR . 'snapshots',
            'db_host'             => $_dbConfig['host'],
            'db_port'             => $_dbConfig['port'],
            'db_name'             => $_dbName,
            'db_user'             => $_dbUser,
            'db_password'         => $_dbPassword,
            'db_config_file_name' => $_dbConfigFile,
            'cluster'             => $_instance->cluster_id,
            'metadata'            => $_md,
        ];
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return bool
     * @throws ProvisioningException
     */
    protected function _deprovisionInstance( $request, $options = [] )
    {
        \Log::debug( '  * rave: deprovision instance' );

        $_storagePath = null;

        //  1. Make a snapshot
        //  2. Delete instance row
        //  3. Drop database
        //  4. Delete storage

        //	Pull the request apart
        $_instance = $request->getInstance();
        $_name = $_instance->instance_name_text;
        $_storageKey = $_instance->storage_id_text;
        $_storage = $request->getStorage();
        $_privatePath = $request->getStorageProvisioner()->getPrivatePath();
        $_ownerPrivatePath = $request->getStorageProvisioner()->getOwnerPrivatePath();
        $_dbConfigFile = $_privatePath . DIRECTORY_SEPARATOR . $_name . '.database.config.php';
        $_instanceMetadata = $_privatePath . DIRECTORY_SEPARATOR . $_name . '.json';

        \Log::debug( '  * rave: deprovision instance > database' );

        //	1. Provision the database
        /** @type RaveDatabaseService $_dbService */
        $_dbService = \App::make( RaveDatabaseServiceProvider::IOC_NAME );

        if ( false === ( $_dbConfig = $_dbService->deprovision( $request ) ) )
        {
            throw new ProvisioningException( 'Failed to deprovision database. Check logs for error.' );
        }

        \Log::debug( '  * rave: deprovision instance > database - complete' );

        if ( !$_instance->delete() )
        {
            throw new \RuntimeException( 'Instance row deletion failed.' );
        }

        \Log::debug( '  * rave: deprovision instance > instance deleted' );

        //  Fire off a "shutdown" event...
        \Log::debug( '  * rave: deprovision instance > fire event' );
        \Event::fire( 'dfe.shutdown', [$this, $request] );

        \Log::debug( '  * rave: deprovision instance - complete' );
    }

}