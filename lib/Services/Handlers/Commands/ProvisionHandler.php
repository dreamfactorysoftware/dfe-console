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

        //  Create the instance record
        $_instance = InstanceManager::make( $command->getInstanceId(), $_options );

        $_guest = IfSet::get( $_options, 'guest-location-nbr' );
        \Log::debug( '  * handling request: ' . print_r( $_options, true ) );

        $_provisioner = Provision::getProvisioner( $_guest );

        if ( empty( $_provisioner ) )
        {
            throw new \RuntimeException( 'The provisioner of the request is not valid.' );
        }

        return $_provisioner->provision( new ProvisioningRequest( $_instance ), $_options );
    }

}
