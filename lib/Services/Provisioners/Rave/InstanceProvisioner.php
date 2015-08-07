<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Contracts\OfferingsAware;
use DreamFactory\Enterprise\Common\Enums\AppKeyClasses;
use DreamFactory\Enterprise\Common\Enums\InstanceStates;
use DreamFactory\Enterprise\Common\Enums\OperationalStates;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceRequest;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceResponse;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\HasOfferings;
use DreamFactory\Enterprise\Common\Traits\HasPrivatePaths;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Enums\ProvisionStates;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Exceptions\SchemaExistsException;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\BaseProvisioner;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Contracts\Filesystem\Filesystem;

class InstanceProvisioner extends BaseProvisioner implements OfferingsAware
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string Our provisioner id
     */
    const PROVISIONER_ID = 'rave';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, HasPrivatePaths, HasOfferings;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    protected function doProvision($request)
    {
        $_output = [];
        $_success = $_result = false;
        $_instance = $request->getInstance();

        //	Update the current instance state
        $_instance->updateState(ProvisionStates::PROVISIONING);

        try {
            //  Provision storage and fill in the request
            $this->provisionStorage($request);

            //  And the instance
            $_result = $this->provisionInstance($request);
            $_instance = $_instance->fresh();
            $_success = true;
        } catch (\Exception $_ex) {
            $this->error('* exception: ' . $_ex->getMessage());

            $_instance->updateState(ProvisionStates::PROVISIONING_ERROR);

            //  Force-kill anything we made before blowing up
            $request->setForced(true);

            $this->deprovisionStorage($request);

            if (!$this->deprovisionInstance($request, ['keep-database' => ($_ex instanceof SchemaExistsException)])) {
                $this->error('* unable to remove instance "' . $_instance->instance_id_text . '" after failed provision.');
            }
        }

        return ProvisionServiceResponse::make($_success,
            $request,
            $_result,
            ['instance' => $_success ? $_instance->toArray() : false,],
            $_output);
    }

    /** @inheritdoc */
    protected function doDeprovision($request, $options = [])
    {
        $_output = [];
        $_success = $_result = false;
        $_instance = $request->getInstance();
        $_original = $_instance->toArray();

        //	Update the current instance state
        $_instance->updateState(ProvisionStates::DEPROVISIONING);

        try {
            $_result = $this->deprovisionInstance($request, $options);
            $_success = true;
        } catch (\Exception $_ex) {
            $_instance->updateState(ProvisionStates::DEPROVISIONING_ERROR);
        }

        return ProvisionServiceResponse::make($_success,
            $request,
            $_result,
            ['instance' => $_success ? $_original : false,],
            $_output);
    }

    /**
     * @param ProvisionServiceRequest $request
     *
     * @return Filesystem
     */
    protected function provisionStorage($request)
    {
        $this->debug('>>> provisioning storage');

        //  Use requested file system if one...
        $_filesystem = $request->getStorage();

        //  Do it!
        $request->setStorageProvisioner(
            $_provisioner = Provision::resolveStorage($request->getInstance()->guest_location_nbr));

        $_provisioner->provision($request);

        $this->debug('<<< provisioning storage - complete');

        return $_filesystem;
    }

    /**
     * @param ProvisionServiceRequest $request
     *
     * @return bool
     */
    protected function deprovisionStorage($request)
    {
        $this->debug('>>> deprovisioning storage');

        //  Use requested file system if one...
        $_filesystem = $request->getStorage();

        //  Do it!
        Provision::resolveStorage($request->getInstance()->guest_location_nbr)->deprovision($request);

        $this->debug('<<< deprovisioning storage - complete');

        return $_filesystem;
    }

    /**
     * @param ProvisionServiceRequest $request
     *
     * @return array
     * @throws ProvisioningException
     */
    protected function provisionInstance($request)
    {
        $_storagePath = null;

        //	Pull the request apart
        $_instance = $request->getInstance();
        $_name = $_instance->instance_name_text;

        $this->debug('>>> provisioning instance "' . $_name . '"');

        $_storageProvisioner = $request->getStorageProvisioner();
        $this->setPrivatePath($_privatePath = $_storageProvisioner->getPrivatePath());
        $this->setOwnerPrivatePath($_ownerPrivatePath = $_storageProvisioner->getOwnerPrivatePath());

        //	1. Provision the database
        $_dbService = Provision::getDatabaseProvisioner($_instance->guest_location_nbr);
        $_dbConfig = $_dbService->provision($request);

        //  2. Generate an app key for the instance
        AppKey::create([
            'key_class_text' => AppKeyClasses::INSTANCE,
            'owner_id'       => $_instance->id,
            'owner_type_nbr' => OwnerTypes::INSTANCE,
            'server_secret'  => config('dfe.security.console-api-key'),
        ]);

        //  3. Update the instance with new provision info
        try {
            $_instance->fill([
                'guest_location_nbr' => GuestLocations::DFE_CLUSTER,
                'instance_id_text'   => $_name,
                'instance_name_text' => $_name,
                'db_host_text'       => $_dbConfig['host'],
                'db_port_nbr'        => $_dbConfig['port'],
                'db_name_text'       => $_dbConfig['database'],
                'db_user_text'       => $_dbConfig['username'],
                'db_password_text'   => $_dbConfig['password'],
                'ready_state_nbr'    => InstanceStates::ADMIN_REQUIRED,
                'state_nbr'          => ProvisionStates::PROVISIONED,
                'platform_state_nbr' => OperationalStates::NOT_ACTIVATED,
                'start_date'         => $_instance->freshTimestamp(),
                'end_date'           => null,
                'terminate_date'     => null,
                'provision_ind'      => true,
                'deprovision_ind'    => false,
            ]);

            //  Create the guest row...
            $_host = $this->getFullyQualifiedDomainName($_name);

            \DB::transaction(function () use ($_instance, $_host) {
                /**
                 * Add guest data if there is a guest record
                 */
                $_instance->guest && $_instance->guest->fill([
                    'base_image_text'   => config('provisioning.base-image',
                        ConsoleDefaults::DFE_CLUSTER_BASE_IMAGE),
                    'vendor_state_nbr'  => ProvisionStates::PROVISIONED,
                    'vendor_state_text' => 'running',
                    'public_host_text'  => $_host,
                ])->save();

                //  Save the instance
                $_instance->save();
            });
        } catch (\Exception $_ex) {
            throw new \RuntimeException('Error updating instance data: ' . $_ex->getMessage());
        }

        //  Fire off a "provisioned" event...
        \Event::fire('dfe.provisioned', [$this, $request, $_instance->getMetadata()]);

        $this->info('<<< provisioning of instance "' . $_name . '" complete');

        return $_instance->getMetadata();
    }

    /**
     * @param ProvisionServiceRequest $request
     * @param array                   $options ['keep-database'=>true|false]
     *
     * @return bool
     * @throws ProvisioningException
     */
    protected function deprovisionInstance($request, $options = [])
    {
        $_instance = $request->getInstance();
        $_keepDatabase = IfSet::get($options, 'keep-database', false);

        if ($_keepDatabase) {
            $this->notice('* "keep-database" specified. Keeping existing schema, if any.');
        } else {
            //	Deprovision the database
            $_dbService = Provision::getDatabaseProvisioner($_instance->guest_location_nbr);

            if (false === ($_dbConfig = $_dbService->deprovision($request))) {
                throw new ProvisioningException('Failed to deprovision database. Check logs for error.');
            }
        }

        try {
            if (!$_instance->delete()) {
                throw new \RuntimeException('Instance row deletion failed.');
            }
        } catch (\Exception $_ex) {
            $this->error('* exception while deleting instance row: ' . $_ex->getMessage());

            return false;
        }

        //  Fire off a "shutdown" event...
        \Event::fire('dfe.deprovisioned', [$this, $request]);

        $this->debug('instance row deleted from database');

        return true;
    }

    /**
     * Builds/returns the fully qualified domain name for an instance
     *
     * @param string $name
     *
     * @return string
     */
    protected function getFullyQualifiedDomainName($name)
    {
        return implode('.',
            [
                trim($name, '. '),
                trim(config('provisioning.default-dns-zone'), '. '),
                trim(config('provisioning.default-dns-domain'), '. '),
            ]);
    }
}