<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use DreamFactory\Enterprise\Services\Provisioners\ProvisionServiceRequest;

/**
 * Processes queued deprovision requests
 */
class DeprovisionJobHandler extends BaseListener
{
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
    public function handle(DeprovisionJob $command)
    {
        $_options = $command->getOptions();
        $this->debug('>>> deprovision "' . ($_instanceId = $command->getInstanceId()) . '" request received');

        try {
            //  Find the instance
            $_instance = $this->findInstance($_instanceId);
        } catch (\Exception $_ex) {
            $this->error('deprovision request failure: instance not found.');

            return false;
        }

        try {
            $_provisioner = Provision::getProvisioner($_instance->guest_location_nbr);

            if (empty($_provisioner)) {
                throw new \RuntimeException('The provisioner of the request is not valid.');
            }

            $_response = $_provisioner->deprovision(new ProvisionServiceRequest($_instance, null, true, false, $_options));

            if (!$_response) {
                throw new ProvisioningException('deprovision failure');
            }

            $this->info('deprovision request complete in ' . number_format($_response->getElapsedTime(), 4) . 's');
            $this->debug('[Deprovision] Instance "' . $_instance->instance_id_text . '" provisioned successfully.');

            $command->setResult($_response);

            return $_response;
        } catch (\Exception $_ex) {
            $this->error('deprovision "' . $command->getInstanceId() . '" request exception: ' . $_ex->getMessage());
        }

        $this->debug('[Deprovision] Instance "' . $command->getInstanceId() . '" deprovision failed.');

        return false;
    }
}
