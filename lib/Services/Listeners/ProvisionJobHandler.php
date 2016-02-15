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
        $_guestLocation = array_get($_options, 'guest-location', config('provisioning.default-guest-location'));

        if (is_string($_guestLocation) && !is_numeric($_guestLocation)) {
            $_options['guest-location'] = GuestLocations::resolve($_guestLocation, true);
            \Log::debug('[Provision] guest location "' . $_options['guest-location'] . '" resolved from "' . $_guestLocation . '".');
            $_guestLocation = $_options['guest-location'];
        }

        \Log::info('[Provision] Handler called', ['guest' => $_guestLocation, 'instance-id' => $command->getInstanceId(), 'options' => $_options]);

        try {
            //  Create the instance record
            $_instance = InstanceManager::make($command->getInstanceId(), $_options);

            if (!$_instance) {
                throw new ProvisioningException('InstanceManager::make() failed');
            }

            $this->notifyJobOwner(ConsoleOperations::PROVISION,
                $_instance->user->email_addr_text,
                trim($_instance->user->first_name_text . ' ' . $_instance->user->last_name_text),
                [
                    'instance' => $_instance->fresh(['user']),
                ]);
        } catch (\Exception $_ex) {
            \Log::error('[Provision] failure, exception creating instance: ' . $_ex->getMessage());

            $_owner = $command->getOwner();

            $this->notifyJobOwner(ConsoleOperations::PROVISION,
                $_owner->email_addr_text,
                trim($_owner->first_name_text . ' ' . $_owner->last_name_text),
                [
                    'instance'     => false,
                    'instanceName' => $command->getInstanceId(),
                ]);

            return false;
        }

        try {
            $_guest = array_get($_options, 'guest-location', config('provisioning.default-guest-location'));
            $_provisioner = Provision::getProvisioner($_guest);

            if (empty($_provisioner)) {
                throw new \RuntimeException('The provisioner of the request is not valid.');
            }

            if (false === ($_response = $_provisioner->provision(new ProvisionServiceRequest($_instance)))) {
                throw new ProvisioningException('provisioning error');
            }

            \Log::info('[Provision] completed in ' . number_format($_response->getElapsedTime(), 4) . 's');

            return $_response;
        } catch (\Exception $_ex) {
            \Log::error('[Provision] failure: ' . $_ex->getMessage());

            //  Delete instance record...
            if (!$_instance->delete()) {
                throw new \LogicException('Unable to remove created instance "' . $_instance->instance_id_text . '".');
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
            \Log::info('[Provision] *api* provision request', $request);

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
                \Log::error('[Provision] *api* error during job run');
            }
        } catch (\Exception $_ex) {
            \Log::error('[Provision] *api* error: ' . $_ex->getMessage());
        }

        return false;
    }
}
