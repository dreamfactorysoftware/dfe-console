<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Exceptions\SchemaExistsException;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\BaseProvisioner;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Fabric\Database\Enums\GuestLocations;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\IfSet;
use DreamFactory\Library\Utility\JsonFile;
use Illuminate\Contracts\Filesystem\Filesystem;

class Provisioner extends BaseProvisioner
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

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
     * @param ProvisioningRequest $request
     * @param array               $options
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
            $this->error( '    * provisioner exception: ' . $_ex->getMessage() );

            $_instance->updateState( ProvisionStates::PROVISIONING_ERROR );

            //  Force-kill anything we made before blowing up
            $request->setForced( true );

            $this->_deprovisionStorage( $request );

            if ( !$this->_deprovisionInstance( $request, ['keep-database' => ( $_ex instanceof SchemaExistsException )] ) )
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
        $_filesystem = $request->getStorage();

        //  Do it!
        $request->setStorageProvisioner( $_provisioner = Provision::resolveStorage( $request->getInstance()->guest_location_nbr ) );
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
        $_filesystem = $request->getStorage();

        //  Do it!
        Provision::resolveStorage( $request->getInstance()->guest_location_nbr )->deprovision( $request );

        \Log::debug( '  * rave: deprovision storage - complete' );

        return $_filesystem;
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
        $_storagePath = null;

        //	Pull the request apart
        $_instance = $request->getInstance();
        $_name = $_instance->instance_name_text;
        $_storageKey = $_instance->storage_id_text;

        \Log::debug( '  * rave: provision "' . $_name . '" - begin' );

        $_storageProvisioner = $request->getStorageProvisioner();
        $_privatePath = $_storageProvisioner->getPrivatePath();
        $_ownerPrivatePath = $_storageProvisioner->getOwnerPrivatePath();

        $_dbConfigFile = $_privatePath . DIRECTORY_SEPARATOR . $_name . '.database.config.php';

        //	1. Provision the database
        $_dbService = Provision::getDatabaseProvisioner( $_instance->guest_location_nbr );
        $_dbConfig = $_dbService->provision( $request );

        $_dbUser = $_dbConfig['username'];
        $_dbPassword = $_dbConfig['password'];
        $_dbName = $_dbConfig['database'];

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
                ]
            );

            //  Collect metadata
            $_md = array_merge(
                $_instance->getMetadata(),
                [
                    'mount'              => $_storageProvisioner->getStorageMap(),
                    'private-path'       => $_privatePath,
                    'owner-private-path' => $_ownerPrivatePath,
                    'storage-path'       => $_storagePath,
                    'db'                 => [$_name => $_dbConfig],
                ]
            );

            $_instanceData = $_instance->instance_data_text;

            if ( empty( $_instanceData ) )
            {
                $_instanceData = [];
            }

            if ( !array_key_exists( 'metadata', $_instanceData ) )
            {
                $_instanceData['metadata'] = $_md;
            }

            $_instance->instance_data_text = $_instanceData;
            $_instance->save();

            //  Save the metadata
            try
            {
                $request->getStorage()->put(
                    $_ownerPrivatePath . DIRECTORY_SEPARATOR . $_instance->instance_name_text . '.json',
                    JsonFile::encode( $_md )
                );
            }
            catch ( \Exception $_ex )
            {
                \Log::error( 'Exception saving instance metadata: ' . $_ex->getMessage() );
            }

            \Log::debug( '    * rave: instance update - complete' );
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Error updating instance data: ' . $_ex->getMessage() );
        }

        //  Fire off a "launch" event...
        \Log::debug( '    * rave: fire "dfe.launch" event' );
        \Event::fire( 'dfe.launch', [$this, $request, $_md] );

        \Log::debug( '  * rave: provision "' . $_name . '" - complete' );

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
     * @param array               $options ['keep-database'=>true|false]
     *
     * @return bool
     * @throws ProvisioningException
     */
    protected function _deprovisionInstance( $request, $options = [] )
    {
        $_instance = $request->getInstance();
        $_keepDatabase = IfSet::get( $options, 'keep-database', false );

        if ( $_keepDatabase )
        {
            $this->notice( '    * rave: not removing existing schema.' );
        }
        else
        {
            //	Deprovision the database
            $_dbService = Provision::getDatabaseProvisioner( $_instance->guest_location_nbr );

            if ( false === ( $_dbConfig = $_dbService->deprovision( $request ) ) )
            {
                throw new ProvisioningException( 'Failed to deprovision database. Check logs for error.' );
            }
        }

        if ( !$_instance->delete() )
        {
            throw new \RuntimeException( 'Instance row deletion failed.' );
        }

        \Log::debug( '    * rave: instance deleted' );

        //  Fire off a "shutdown" event...
        \Event::fire( 'dfe.shutdown', [$this, $request] );
        \Log::debug( '    * rave: event "dfe.shutdown" fired' );

        return true;
    }

}