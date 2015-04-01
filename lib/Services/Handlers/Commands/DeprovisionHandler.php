<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Enums\Provisioners;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Commands\DeprovisionJob;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\DreamFactoryRave;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;

/**
 * Processes queued deprovision requests
 */
class DeprovisionHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a deprovisioning request
     *
     * @param DeprovisionJob $command
     *
     * @return bool|mixed
     */
    public function handle( DeprovisionJob $command )
    {
        $_options = $command->getOptions();
        \Log::debug( 'dfe: deprovision instance - begin' );

        try
        {
            //  Find the instance
            $_instance = $this->_findInstance( $command->getInstanceId() );
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'dfe: deprovision instance - failure, exception creating instance: ' . $_ex->getMessage() );

            return false;
        }

        try
        {
            $_provisioner = Provision::getProvisioner( $_instance->guest_location_nbr );

            if ( empty( $_provisioner ) )
            {
                throw new \RuntimeException( 'The provisioner of the request is not valid.' );
            }

            $_result = $_provisioner->deprovision( new ProvisioningRequest( $_instance, null, true ), $_options );

            \Log::debug( 'dfe: deprovision instance - complete' );

            return $_result;
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'dfe: deprovision instance - failure, exception during deprovisioning: ' . $_ex->getMessage() );
        }

        \Log::debug( 'dfe: provision instance - fail' );

        return false;
    }

}
