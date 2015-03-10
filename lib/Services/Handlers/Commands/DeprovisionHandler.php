<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Enums\Provisioners;
use DreamFactory\Enterprise\Services\Commands\DeprovisionJob;
use DreamFactory\Enterprise\Services\Provisioners\DreamFactoryRave;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Enterprise\Services\Traits\InstanceValidation;

/**
 * Processes queued deprovision requests
 */
class DeprovisionHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a deprovisioning request
     *
     * @param DeprovisionJob $command
     *
     */
    public function handle( DeprovisionJob $command )
    {
        $_provisioner = null;

        //  Get the instance record
        $_instance = $this->_validateInstance( $command->getInstanceId() );

        switch ( $_instance->guest_location_nbr )
        {
            case Provisioners::DREAMFACTORY_ENTERPRISE:
                $_provisioner = new DreamFactoryRave();
                break;

            case Provisioners::AMAZON_EC2:
                //  Not supported at this time
                //$_provisioner = new AmazonEc2();
                break;

            case Provisioners::MICROSOFT_AZURE:
                //  Not supported at this time
                //$_provisioner = new MicrosoftAzure();
                break;
        }

        if ( empty( $_provisioner ) )
        {
            throw new \RuntimeException( 'The provisioner of the request is not valid.' );
        }

        $_provisioner->deprovision( ProvisioningRequest::createFromInstance( $_instance ) );
    }

}
