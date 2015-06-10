<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\InstanceArchive;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Commands\DeprovisionJob;
use DreamFactory\Enterprise\Services\Commands\ExportJob;
use DreamFactory\Enterprise\Services\Commands\ImportJob;
use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use DreamFactory\Enterprise\Services\Contracts\HasOfferings;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class OpsController extends Controller
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var string
     */
    protected $_instanceName;
    /**
     * @type User
     */
    protected $_user;
    /**
     * @type string
     */
    protected $_clientId;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /**
     * ctor
     */
    public function __construct()
    {
        $this->middleware('auth.client');
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function postStatus(Request $request)
    {
        $_archived = false;
        $_id = $request->input('id');

        try {
            $_owner = $this->_validateOwner($request);
            $_instance = $this->_findInstance($request->input('id'));

            if ($_owner->type < OwnerTypes::CONSOLE && $_instance->user_id != $_owner->id) {
                return ErrorPacket::create(Response::HTTP_NOT_FOUND,
                    'Instance not found, invalid owner (' . $_owner->id . ').');
            }
        } catch (\Exception $_ex) {
            //  Check the deleted instances
            if (null === ($_instance = InstanceArchive::byNameOrId($_id)->first())) {
                return ErrorPacket::create(Response::HTTP_NOT_FOUND, 'Instance not found.');
            }

            $_archived = true;
        }

        $_rootStoragePath = $_instance->getRootStoragePath();
        $_storagePath = $_instance->getStoragePath();

        /**
         * This has multiple copies of data because it is used by several different systems
         */

        return SuccessPacket::make(
            array(
                'id' => $_instance->id,
                'archived' => $_archived,
                'deleted' => false,
                'metadata' => (array)$_instance->instance_data_text,
                'root-storage-path' => $_rootStoragePath,
                'storage-path' => $_storagePath,
                'owner-private-path' => $_rootStoragePath . DIRECTORY_SEPARATOR . '.private',
                'private-path' => $_storagePath . DIRECTORY_SEPARATOR . '.private',
                'home-links' => config('links'),
                //  snake
                'instance_name_text' => $_instance->instance_name_text,
                'instance_id_text' => $_instance->instance_id_text,
                'state_nbr' => $_instance->state_nbr,
                'vendor_state_nbr' => $_instance->vendor_state_nbr,
                'vendor_state_text' => $_instance->vendor_state_text,
                'provision_ind' => (1 == $_instance->provision_ind),
                'trial_instance_ind' => (1 == $_instance->trial_instance_ind),
                'deprovision_ind' => (1 == $_instance->deprovision_ind),
                'start_date' => (string)$_instance->start_date,
                'create_date' => (string)$_instance->create_date,
                //  camel
                'instanceName' => $_instance->instance_name_text,
                'instanceId' => $_instance->id,
                'vendorInstanceId' => $_instance->instance_id_text,
                'instanceState' => $_instance->state_nbr,
                'vendorState' => $_instance->vendor_state_nbr,
                'vendorStateName' => $_instance->vendor_state_text,
                'provisioned' => (1 == $_instance->provision_ind),
                'trial' => (1 == $_instance->trial_instance_ind),
                'deprovisioned' => (1 == $_instance->deprovision_ind),
                'startDate' => (string)$_instance->start_date,
                'createDate' => (string)$_instance->create_date,
                //  morse
                'instance-id' => $_instance->id,
                'vendor-instance-id' => $_instance->instance_id_text,
                'instance-name' => $_instance->instance_name_text,
                'instance-state' => $_instance->state_nbr,
                'vendor-state' => $_instance->vendor_state_nbr,
                'vendor-state-name' => $_instance->vendor_state_text,
                'start-date' => (string)$_instance->start_date,
                'create-date' => (string)$_instance->create_date,
            )
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function postInstances(Request $request)
    {
        $_owner = $this->_validateOwner($request);

        $_response = array();

        $_instances = Instance::userId($_owner->id)->get();

        if (!empty($_instances)) {
            /** @type Instance $_instance */
            foreach ($_instances as $_instance) {
                if (!empty($_instance->instance_name_text)) {
                    $_response[$_instance->instance_name_text] = $_instance->toArray();
                }

                unset($_instance);
            }
        }

        return SuccessPacket::make($_response);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function postProvisioners(Request $request)
    {
        try {
            $_response = [];
            $_provisioners = Provision::getProvisioners();

            foreach ($_provisioners as $_tag => $_provisioner) {
                $_offerings = false;

                if ($_provisioner instanceof HasOfferings) {
                    foreach ($_provisioner->getOfferings() as $_name => $_config) {
                        $_offerings[$_name] = $_config;
                    }
                }

                $_response[$_tag] = [
                    'id' => $_tag,
                    'offerings' => $_offerings,
                ];
            }

            return SuccessPacket::make($_response);
        } catch (\Exception $_ex) {
            return ErrorPacket::create($_ex);
        }
    }

    /**
     * Provision an instance...
     *
     * @param Request $request
     *
     * @return array
     */
    public function postProvision(Request $request)
    {
        try {
            $_payload = $request->input();
            $_job = new ProvisionJob($request->input('instance-id'), $_payload);

            \Queue::push($_job);

            try {
                $_instance = $this->_findInstance($_job->getInstanceId());
                $_data = $_instance->instance_data_text;
                $_result = IfSet::get($_data, '.provisioning');
                unset($_data['.provisioning']);

                if (!$_instance->update(['instance_data_text' => $_data])) {
                    throw new \RuntimeException('Unable to update instance row.');
                }

                if (!isset($_result['instance'])) {
                    throw new \RuntimeException('The provisioning information is incomplete. Bailing.');
                }

                return SuccessPacket::make($_result['instance']);
            } catch (ModelNotFoundException $_ex) {
                throw new \Exception('Instance not found after provisioning.');
            }
        } catch (\Exception $_ex) {
            \Log::debug('Queuing error: ' . $_ex->getMessage());

            return ErrorPacket::make(null, $_ex->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR, $_ex);
        }
    }

    /**
     * Deprovision an instance...
     *
     * @param Request $request
     *
     * @return array
     */
    public function postDeprovision(Request $request)
    {
        try {
            $_payload = $request->input();
            $_job = new DeprovisionJob($request->input('instance-id'), $_payload);
            \Queue::push($_job);

            return SuccessPacket::make($_job->getResult());
        } catch (\Exception $_ex) {
            \Log::debug('Queuing error: ' . $_ex->getMessage());

            return ErrorPacket::make(null, $_ex->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR, $_ex);
        }
    }

    /**
     * Import an instance
     *
     * @param Request $request
     *
     * @return array
     */
    public function postImport(Request $request)
    {
        try {
            $_payload = $request->input();
            $_job = new ImportJob($request->input('instance-id'), $_payload);
            \Queue::push($_job);

            return SuccessPacket::make($_job->getResult());
        } catch (\Exception $_ex) {
            \Log::debug('Queuing error: ' . $_ex->getMessage());

            return ErrorPacket::make(null, $_ex->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR, $_ex);
        }
    }

    /**
     * Export an instance
     *
     * @param Request $request
     *
     * @return array
     */
    public function postExport(Request $request)
    {
        try {
            $_payload = $request->input();
            $_job = new ExportJob($request->input('instance-id'), $_payload);
            \Queue::push($_job);

            return SuccessPacket::make($_job->getResult());
        } catch (\Exception $_ex) {
            \Log::debug('Queuing error: ' . $_ex->getMessage());

            return ErrorPacket::make(null, $_ex->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR, $_ex);
        }
    }

    /**
     * Allows partners to post data in a generic way.
     * @param string $command
     * @param Request $request
     * @return array
     */
    public function postPartner($command, Request $request)
    {
        if (empty($command))
            return ErrorPacket::create(Response::HTTP_BAD_REQUEST);

        if (null === ($_partnerId = $request->input('pid')) || null === ($_partner = config('partners.' . $_partnerId)))
            return ErrorPacket::create(Response::HTTP_UNAUTHORIZED);

        if (!in_array($command, IfSet::get($_partner, 'commands', [])))
            return ErrorPacket::create(Response::HTTP_FORBIDDEN);

        /**
         * @todo Insert dynamic partner handler here
         */

        ErrorPacket::create(Response::HTTP_I_AM_A_TEAPOT);
    }

    /**
     * @param Request $request
     *
     * @return User
     */
    protected function _validateOwner(Request $request)
    {
        /** auth.client middleware registers a user resolver with the request for us */
        $_owner = $request->user();

        if (empty($_owner)) {
            throw new \RuntimeException('Invalid credentials');
        }

        return $_owner;
    }
}
