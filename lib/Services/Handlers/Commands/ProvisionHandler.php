<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Containers\RaveBox;
use DreamFactory\Enterprise\Common\Contracts\ProvisionerFactory;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Managers\InstanceManager;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\IfSet;

/**
 * Processes queued provision requests
 */
class ProvisionHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a provisioning request
     *
     * @param  ProvisionJob $command
     *
     * @return mixed
     */
    public function handle( ProvisionJob $command )
    {
        $_options = $command->getOptions();
        \Log::debug( 'rave: provision instance - begin' );

        try
        {
            //  Create the instance record
            $_instance = InstanceManager::make( $command->getInstanceId(), $_options );
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'rave: provision instance - failure, exception creating instance: ' . $_ex->getMessage() );

            return false;
        }

        try
        {
            $_guest = IfSet::get( $_options, 'guest-location-nbr', config( 'dfe.provisioning.default-guest-location' ) );
            $_provisioner = Provision::getProvisioner( $_guest );

            if ( empty( $_provisioner ) )
            {
                throw new \RuntimeException( 'The provisioner of the request is not valid.' );
            }

            $_result = $_provisioner->provision( new ProvisioningRequest( $_instance ), $_options );

            \Log::debug( 'rave: provision instance - complete: ' . print_r( $_result, true ) );

            return $_result;
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'exception during provisioning: ' . $_ex->getMessage() );

            //  Delete instance record...
            if ( !$_instance->delete() )
            {
                throw new \LogicException( 'Unable to remove created instance "' . $_instance->instance_id_text . '".' );
            }
        }

        \Log::debug( 'rave: provision instance - fail' );

        return false;
    }

}
