<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;

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
        $this->setLumberjackPrefix('dfe.deprovision');

        $_options = $command->getOptions();
        $this->debug('>>> deprovision "' . $command->getInstanceId() . '" request received');

        try {
            //  Find the instance
            $_instance = $this->_findInstance($command->getInstanceId());
        } catch (\Exception $_ex) {
            $this->error('deprovision request failure: instance not found.');

            return false;
        }

        try {
            $_provisioner = Provision::getProvisioner($_instance->guest_location_nbr);

            if (empty($_provisioner)) {
                throw new \RuntimeException('The provisioner of the request is not valid.');
            }

            $_result = $_provisioner->deprovision(new ProvisioningRequest($_instance, null, true), $_options);

            if (is_array($_result) && $_result['success'] && isset($_result['elapsed'])) {
                $this->debug('deprovision request complete in ' . number_format($_result['elapsed'], 4) . 's');
            }

            $this->debug('<<< deprovision "' . $command->getInstanceId() . '" request SUCCESS');

            $command->setResult($_result);

            return $_result;
        } catch (\Exception $_ex) {
            $this->error('deprovision "' . $command->getInstanceId() . '" request exception: ' . $_ex->getMessage());
        }

        $this->debug('<<< deprovision "' . $command->getInstanceId() . '" request FAILURE');

        return false;
    }

}
