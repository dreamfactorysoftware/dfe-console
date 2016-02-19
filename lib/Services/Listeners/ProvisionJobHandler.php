<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Console\Enums\ConsoleOperations;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Facades\InstanceManager;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;
use DreamFactory\Enterprise\Services\Provisioners\ProvisionServiceRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Processes queued provision requests
 */
class ProvisionJobHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation, Notifier;

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
        $_owner = OwnerTypes::getOwner(array_get($_options, 'owner-id'), $_ownerType);
        $_instanceId = $command->getInstanceId();
        $_guestLocation = array_get($_options, 'guest-location', config('provisioning.default-guest-location'));

        if (is_string($_guestLocation) && !is_numeric($_guestLocation)) {
            $_options['guest-location'] = GuestLocations::resolve($_guestLocation, true);
            \Log::debug('[dfe.provision-job-handler.handle] guest location "' . $_options['guest-location'] . '" resolved from "' . $_guestLocation . '".');
            $_guestLocation = $_options['guest-location'];
        }

        \Log::info('[dfe.provision-job-handler.handle] Handler called', ['guest' => $_guestLocation, 'instance-id' => $_instanceId, 'options' => $_options]);

        try {
            //  Create the instance record
            $_instance = InstanceManager::make($_instanceId, $_options);

            if (!$_instance) {
                throw new ProvisioningException('InstanceManager::make() failed');
            }
        } catch (\Exception $_ex) {
            \Log::error('[dfe.provision-job-handler.handle] failure, exception creating instance: ' . $_ex->getMessage());

            $this->notifyJobOwner(ConsoleOperations::PROVISION,
                $_owner->email_addr_text,
                trim($_owner->first_name_text . ' ' . $_owner->last_name_text),
                [
                    'instance'     => false,
                    'instanceName' => $command->getInstanceId(),
                    'firstName'    => $_owner->first_name_text,
                ]);

            return false;
        }

        try {
            $_provisioner = Provision::getProvisioner($_guestLocation);

            if (empty($_provisioner)) {
                throw new \RuntimeException('The provisioner of the request is not valid.');
            }

            if (false === ($_response = $_provisioner->provision(new ProvisionServiceRequest($_instance)))) {
                throw new ProvisioningException('provisioning error');
            }

            \Log::info('[dfe.provision-job-handler.handle] completed in ' . number_format($_response->getElapsedTime(), 4) . 's');

            $this->notifyJobOwner(ConsoleOperations::PROVISION,
                $_owner->email_addr_text,
                trim($_owner->first_name_text . ' ' . $_owner->last_name_text),
                [
                    'instance' => $_instance->fresh(['user']),
                ]);

            return $_response;
        } catch (\Exception $_ex) {
            \Log::error('[dfe.provision-job-handler.handle] failure: ' . $_ex->getMessage());

            $this->notifyJobOwner(ConsoleOperations::PROVISION,
                $_owner->email_addr_text,
                trim($_owner->first_name_text . ' ' . $_owner->last_name_text),
                [
                    'instance'     => false,
                    'instanceName' => $command->getInstanceId(),
                    'firstName'    => $_owner->first_name_text,
                ]);

            //  Delete instance record...
            if (!$_instance->delete()) {
                throw new \RuntimeException('Unable to remove created instance "' . $_instance->instance_id_text . '".');
            }
        }

        return false;
    }

    /**
     * @param array $request
     *
     * @return bool|\DreamFactory\Enterprise\Database\Models\Instance
     */
    public static function provision(array $request)
    {
        try {
            \Log::info('[dfe.provision-job-handler.handle] *api* provision request', $request);

            $_instanceId = array_get($request, 'instance-id');
            $_ownerType = array_get($request, 'owner-type', OwnerTypes::USER);
            $_ownerId = array_get($request, 'owner-id');
            $_guestLocation = array_get($request, 'guest-location', GuestLocations::DFE_CLUSTER);

            $_job = new ProvisionJob($_instanceId, [
                'guest-location' => $_guestLocation,
                'owner-id'       => $_ownerId,
                'owner-type'     => $_ownerType,
                'cluster-id'     => array_get($request, 'cluster-id', config('dfe.cluster-id')),
            ]);

            \Queue::push($_job);

            try {
                return Instance::byNameOrId($_job->getInstanceId())->firstOrFail();
            } catch (ModelNotFoundException $_ex) {
                \Log::error('[dfe.provision-job-handler.provision] *api* error during job run');
            }
        } catch (\Exception $_ex) {
            \Log::error('[dfe.provision-job-handler.provision] *api* error: ' . $_ex->getMessage());
        }

        return false;
    }
}
