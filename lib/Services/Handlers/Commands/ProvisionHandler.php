<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Containers\RaveBox;
use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Traits\InstanceValidation;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

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
        $_provisioner = null;

        //  Create the instance record
        $_instance = Instance::make( $command->getInstanceId(), $command->getOptions() );

        switch ( $_instance->guest_location_nbr )
        {
            case GuestLocations::DFE_CLUSTER:
                $_provisioner = new InstanceProvisioner();
                break;

            case GuestLocations::AMAZON_EC2:
                //  Not supported at this time
                //$_provisioner = new AmazonEc2();
                break;

            case GuestLocations::MICROSOFT_AZURE:
                //  Not supported at this time
                //$_provisioner = new MicrosoftAzure();
                break;
        }

        if ( empty( $_provisioner ) )
        {
            throw new \RuntimeException( 'The provisioner of the request is not valid.' );
        }

        return $_provisioner->provision( ProvisioningRequest::createFromInstance( $_instance ) );
    }

}
