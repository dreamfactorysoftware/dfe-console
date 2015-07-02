<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Provisioners\ProvisioningRequest;
use DreamFactory\Enterprise\Database\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Facades\InstanceManager;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;
use DreamFactory\Library\Utility\IfSet;

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
            $_guest = IfSet::get($_options, 'guest-location-nbr', config('dfe.provisioning.default-guest-location'));
            $_provisioner = Provision::getProvisioner($_guest);

            if (empty($_provisioner)) {
                throw new \RuntimeException('The provisioner of the request is not valid.');
            }

            $_result = $_provisioner->provision(new ProvisioningRequest($_instance), $_options);

            if (is_array($_result) && $_result['success'] && isset($_result['elapsed'])) {
                \Log::info('provisioning - success, completed in ' . number_format($_result['elapsed'], 4) . 's');
            }

            return true;
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
