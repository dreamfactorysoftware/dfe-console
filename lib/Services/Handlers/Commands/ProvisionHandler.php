<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Database\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Facades\InstanceManager;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
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
        \Log::debug( 'dfe: ProvisionJob - begin' );

        try
        {
            //  Create the instance record
            $_instance = InstanceManager::make( $command->getInstanceId(), $_options );

            if ( !$_instance )
            {
                throw new ProvisioningException( 'InstanceManager::make() failed' );
            }
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'dfe: ProvisionJob - failure, exception creating instance: ' . $_ex->getMessage() );

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

            if ( is_array( $_result ) && $_result['success'] && isset( $_result['elapsed'] ) )
            {
                \Log::debug( '  * completed in ' . number_format( $_result['elapsed'], 4 ) . 's' );
            }

            \Log::debug( 'dfe: ProvisionJob - complete: ' . print_r( $_result, true ) );

            $command->setResult( $_result );

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

        \Log::debug( 'dfe: ProvisionJob - fail' );

        return false;
    }

}
