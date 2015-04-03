<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Commands\DeprovisionJob;
use DreamFactory\Enterprise\Services\Facades\Provision;
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
        \Log::debug( 'dfe: DeprovisionJob - begin' );

        try
        {
            //  Find the instance
            $_instance = $this->_findInstance( $command->getInstanceId() );
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'dfe: DeprovisionJob - failure, instance not found.' );

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

            if ( is_array( $_result ) && $_result['success'] && isset( $_result['elapsed'] ) )
            {
                \Log::debug( '  * completed in ' . number_format( $_result['elapsed'], 4 ) . 's' );
            }

            \Log::debug( 'dfe: DeprovisionJob - complete' );

            return $_result;
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'dfe: DeprovisionJob - failure, exception during deprovisioning: ' . $_ex->getMessage() );
        }

        \Log::debug( 'dfe: DeprovisionJob - fail' );

        return false;
    }

}
