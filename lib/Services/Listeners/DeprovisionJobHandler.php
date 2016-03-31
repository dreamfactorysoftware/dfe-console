<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Console\Enums\ConsoleOperations;
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
    //* Traits
    //******************************************************************************

    use Notifier;

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
            $this->error('[Deprovision] request failure: instance not found.');

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

            $this->debug('[Deprovision] request complete in ' . number_format($_response->getElapsedTime(), 4) . 's');
            $this->info('[Deprovision] Instance "' . $_instance->instance_id_text . '" deprovisioned successfully.');

            $command->setResult($_response);

            $this->notifyJobOwner(ConsoleOperations::DEPROVISION,
                $_instance->user->email_addr_text,
                trim($_instance->user->first_name_text . ' ' . $_instance->user->last_name_text),
                [
                    'instance'     => $_instance->fresh(['user']),
                    'instanceName' => $_instance->instance_id_text,
                ]);

            return $_response;
        } catch (\Exception $_ex) {
            $this->error('[Deprovision] deprovision "' . $command->getInstanceId() . '" request exception: ' . $_ex->getMessage());

            $_owner = $command->getOwner();

            $this->notifyJobOwner(ConsoleOperations::PROVISION,
                $_owner->email_addr_text,
                trim($_owner->first_name_text . ' ' . $_owner->last_name_text),
                [
                    'instance'      => false,
                    'instanceName'  => $command->getInstanceId(),
                    'headTitle'     => 'Deprovision Error',
                    'contentHeader' => 'Your instance was not retired',
                ]);
        }

        $this->debug('[Deprovision] Instance "' . $command->getInstanceId() . '" deprovision failed.');

        return false;
    }
}
