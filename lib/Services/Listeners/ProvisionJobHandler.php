<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceRequest;
use DreamFactory\Enterprise\Database\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Facades\InstanceManager;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;

/**
 * Processes queued provision requests
 */
class ProvisionJobHandler
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
     * @param ProvisionJob $command
     *
     * @return mixed
     */
    public function handle(ProvisionJob $command)
    {
        $_options = $command->getOptions();

        try {
            //  Create the instance record
            $_instance = InstanceManager::make($command->getInstanceId(), $_options);

            if (!$_instance) {
                throw new ProvisioningException('InstanceManager::make() failed');
            }
        } catch (\Exception $_ex) {
            \Log::error('provisioning - failure, exception creating instance: ' . $_ex->getMessage());

            return false;
        }

        try {
            $_guest = array_get($_options, 'guest-location-nbr', config('provisioning.default-guest-location'));
            $_provisioner = Provision::getProvisioner($_guest);

            if (empty($_provisioner)) {
                throw new \RuntimeException('The provisioner of the request is not valid.');
            }

            if (false === ($_response = $_provisioner->provision(new ProvisionServiceRequest($_instance)))) {
                throw new ProvisioningException('provisioning error');
            }

            \Log::info('provisioning - success, completed in ' . number_format($_response->getElapsed(), 4) . 's');

            return $_response;
        } catch (\Exception $_ex) {
            \Log::error('provisioning - failure, exception during provisioning: ' . $_ex->getMessage());

            //  Delete instance record...
            if (!$_instance->delete()) {
                throw new \LogicException('Unable to remove created instance "' . $_instance->instance_id_text . '".');
            }
        }

        return false;
    }
}
