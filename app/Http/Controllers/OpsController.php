<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Contracts\IsVersioned;
use DreamFactory\Enterprise\Common\Contracts\OfferingsAware;
use DreamFactory\Enterprise\Common\Enums\AppKeyClasses;
use DreamFactory\Enterprise\Common\Exceptions\RegistrationException;
use DreamFactory\Enterprise\Common\Facades\InstanceStorage;
use DreamFactory\Enterprise\Common\Http\Controllers\BaseController;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Versioned;
use DreamFactory\Enterprise\Console\Http\Middleware\AuthenticateOpsClient;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\InstanceArchive;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Partner\AlertPartner;
use DreamFactory\Enterprise\Partner\Facades\Partner;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;
use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Enterprise\Services\UsageService;
use DreamFactory\Library\Utility\Json;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OpsController extends BaseController implements IsVersioned
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, Versioned;

    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var string
     */
    protected $instanceName;
    /**
     * @type string
     */
    protected $clientId;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /**
     * ctor
     */
    public function __construct()
    {
        $this->middleware(AuthenticateOpsClient::ALIAS, ['except' => 'postPartner',]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function getMetrics(Request $request)
    {
        /** @type UsageService $_service */
        $_service = \App::make(UsageServiceProvider::IOC_NAME);
        $_stats = $_service->gatherStatistics();

        if (empty($_stats)) {
            return $this->failure(Response::HTTP_INTERNAL_SERVER_ERROR, 'No stats returned from service.');
        }

        return $this->success($_stats);
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
                return $this->failure(Response::HTTP_NOT_FOUND, 'Instance not found.');
            }
        } catch (\Exception $_ex) {
            //  Check the deleted instances
            if (null === ($_instance = InstanceArchive::byNameOrId($_id)->first())) {
                return $this->failure(Response::HTTP_NOT_FOUND, 'Instance not found.');
            }

            $_archived = true;
        }

        $_base = [
            'id'                 => $_instance->id,
            'archived'           => $_archived,
            'deleted'            => false,
            'metadata'           => Instance::makeMetadata($_instance),
            'root-storage-path'  => InstanceStorage::getStorageRootPath(),
            'storage-path'       => $_instance->getStoragePath(),
            'owner-private-path' => $_instance->getOwnerPrivatePath(),
            'private-path'       => $_instance->getPrivatePath(),
            'home-links'         => config('links', []),
            //  morse
            'instance-id'        => $_instance->instance_name_text,
            'vendor-instance-id' => $_instance->instance_id_text,
            'instance-name'      => $_instance->instance_name_text,
            'instance-state'     => $_instance->state_nbr,
            'vendor-state'       => $_instance->vendor_state_nbr,
            'vendor-state-name'  => $_instance->vendor_state_text,
            'start-date'         => (string)$_instance->start_date,
            'create-date'        => (string)$_instance->create_date,
        ];

        switch ($this->getRequestedVersion($request)) {
            case 2:     //  v2 is base
                $_merge = null;
                break;

            default:    //  All else is original + base
                $_merge = [
                    //  snake
                    'instance_name_text' => $_instance->instance_name_text,
                    'instance_id_text'   => $_instance->instance_id_text,
                    'state_nbr'          => $_instance->state_nbr,
                    'vendor_state_nbr'   => $_instance->vendor_state_nbr,
                    'vendor_state_text'  => $_instance->vendor_state_text,
                    'provision_ind'      => (1 == $_instance->provision_ind),
                    'trial_instance_ind' => (1 == $_instance->trial_instance_ind),
                    'deprovision_ind'    => (1 == $_instance->deprovision_ind),
                    'start_date'         => (string)$_instance->start_date,
                    'create_date'        => (string)$_instance->create_date,
                    //  camel
                    'instanceName'       => $_instance->instance_name_text,
                    'instanceId'         => $_instance->id,
                    'vendorInstanceId'   => $_instance->instance_id_text,
                    'instanceState'      => $_instance->state_nbr,
                    'vendorState'        => $_instance->vendor_state_nbr,
                    'vendorStateName'    => $_instance->vendor_state_text,
                    'provisioned'        => (1 == $_instance->provision_ind),
                    'trial'              => (1 == $_instance->trial_instance_ind),
                    'deprovisioned'      => (1 == $_instance->deprovision_ind),
                    'startDate'          => (string)$_instance->start_date,
                    'createDate'         => (string)$_instance->create_date,
                ];
                break;
        }

        /**
         * This has multiple copies of data because it is used by several different systems
         */

        return $this->success(array_merge($_base, $_merge ?: []));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function postInstances(Request $request)
    {
        $_owner = $this->_validateOwner($request);

        $_response = [];

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

        return $this->success($_response);
    }

    /**
     * @return array
     */
    public function postProvisioners()
    {
        try {
            $_response = [];
            $_provisioners = Provision::getProvisioners();

            foreach ($_provisioners as $_tag => $_provisioner) {
                $_offerings = false;

                if ($_provisioner instanceof OfferingsAware) {
                    foreach ($_provisioner->getOfferings() as $_name => $_config) {
                        $_offerings[$_name] = $_config;
                    }
                }

                $_response[$_tag] = [
                    'id'        => $_tag,
                    'offerings' => $_offerings,
                ];
            }

            return $this->success($_response);
        } catch (\Exception $_ex) {
            return $this->failure($_ex);
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

            $this->debug('provision "' .
                $request->input('instance-id') .
                '" request received with payload: ' .
                Json::encode($_payload));

            \Queue::push($_job);

            try {
                return $this->success($this->_findInstance($_job->getInstanceId()));
            } catch (ModelNotFoundException $_ex) {
                throw new \Exception('Instance not found after provisioning.');
            }
        } catch (\Exception $_ex) {
            $this->error('Provision error: ' . $_ex->getMessage());

            return $this->failure($_ex);
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

            return $this->success($_job->getResult());
        } catch (\Exception $_ex) {
            $this->debug('Queuing error: ' . $_ex->getMessage());

            return $this->failure($_ex);
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
        logger('import input=[' . json_encode($request->input()));

        try {
            $_instanceId = $request->input('instance-id');
            $_snapshotId = $request->input('snapshot-id');

            try {
                $_snapshot = $this->_findSnapshot($_snapshotId);
            } catch (ModelNotFoundException $_ex) {
                return $this->failure(Response::HTTP_NOT_FOUND, 'Snapshot "' . $_snapshotId . '" not found.');
            }

            try {
                if ($this->_findInstance($_instanceId)) {
                    return $this->failure(Response::HTTP_CONFLICT, 'Instance already exists.');
                }
            } catch (ModelNotFoundException $_ex) {
                //  We want this...
            }

            $_result = \Artisan::call('dfe:import',
                [
                    'instance-id'   => $_instanceId,
                    'snapshot'      => $_snapshot->snapshot_id_text,
                    'owner-id'      => $_snapshot->user_id,
                    '--owner-type'  => OwnerTypes::USER,
                    '--snapshot-id' => true,
                ]);

            if (0 != $_result) {
                return $this->failure(Response::HTTP_SERVICE_UNAVAILABLE);
            }

            return $this->success($_result);
        } catch (\Exception $_ex) {
            $this->error($_ex->getMessage());

            return $this->failure($_ex);
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
            $_result = \Artisan::call('dfe:export', $request->input());

            if (0 != $_result) {
                return $this->failure(Response::HTTP_SERVICE_UNAVAILABLE);
            }

            return $this->success($_result);
        } catch (\Exception $_ex) {
            $this->error($_ex->getMessage());

            return $this->failure($_ex);
        }
    }

    /**
     * Allows partners to post data in a generic way.
     *
     * @param Request $request
     *
     * @return array
     */
    public function postPartner(Request $request)
    {
        $_pid = $request->input('pid');
        $_command = strtolower(trim($request->input('command')));

        //  Register our partners
        foreach (config('dfe.allowed-partners', []) as $_partnerId) {
            try {
                Partner::resolve($_partnerId);
            } catch (\InvalidArgumentException $_ex) {
                if (null !== ($_config = config('partner.' . $_partnerId)) && !empty($_config)) {
                    Partner::register($_partnerId, new AlertPartner($_partnerId, $_config));
                }
            }
        }

        try {
            $_allowed = Partner::resolve($_pid)->getPartnerDetail('commands', []);

            if (empty($_allowed) || empty($_command) || !in_array($_command, $_allowed)) {
                $this->error('request with invalid command "' . $_command . '"',
                    ['channel' => 'ops.partner', 'allowed' => $_allowed, 'payload' => $request->input()]);

                return $this->failure(Response::HTTP_FORBIDDEN);
            }

            switch ($_command) {
                case 'register':
                    $_response = $this->registerDashboardUser($request);
                    break;

                default:
                    $_response = Partner::request($_pid, $request);
                    break;
            }

            return $this->success($_response);
        } catch (\Exception $_ex) {
            $_payload = $request->input();
            unset($_payload['password']);

            $this->error('failed request for partner id "' .
                $_pid .
                '": ' .
                $_ex->getCode() .
                ' - ' .
                $_ex->getMessage(),
                ['channel' => 'ops.partner', 'payload' => $_payload]);

            return $this->failure(Response::HTTP_BAD_REQUEST, $_ex->getMessage());
        }
    }

    /**
     * @param Request $request
     *
     * @return User
     */
    protected function _validateOwner(Request $request)
    {
        /** middleware registers a user resolver with the request for us */
        $_owner = $request->user();

        if (empty($_owner)) {
            throw new \RuntimeException('Invalid credentials');
        }

        return $_owner;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return User
     * @throws \Exception
     */
    protected function registerDashboardUser(Request $request)
    {
        $_email = $request->input('email');
        $_first = $request->input('firstname');
        $_last = $request->input('lastname');
        $_password = $request->input('password');

        if (empty($_email) || empty($_password) || empty($_first) || empty($_last)) {
            $this->error('missing required fields from partner post',
                ['channel' => 'ops.partner', 'payload' => $request->input()]);

            throw new \InvalidArgumentException('Missing required fields');
        }

        if (false === filter_var($_email, FILTER_VALIDATE_EMAIL)) {
            $this->error('invalid email address "' . $_email . '"',
                ['channel' => 'ops.partner', 'payload' => $request->input()]);

            throw new \InvalidArgumentException('Email address invalid');
        }

        //  See if we know this cat...
        if (null !== ($_user = User::byEmail($_email)->first())) {
            //  Existing user found, don't add to database...
            $_values = $_user->toArray();
            unset($_values['password_text'], $_values['external_password_text']);

            $this->info('existing user attempting registration through partner api',
                ['channel' => 'ops.partner', 'user' => $_values]);

            return $_user;
        }

        //  Create a user account
        try {
            $_user = \DB::transaction(function () use ($request, $_first, $_last, $_email, $_password){
                $_user = User::create([
                    'first_name_text'   => $_first,
                    'last_name_text'    => $_last,
                    'email_addr_text'   => $_email,
                    'nickname_text'     => $request->input('nickname', $_first),
                    'password_text'     => bcrypt($_password),
                    'phone_text'        => $request->input('phone'),
                    'company_name_text' => $request->input('company'),
                ]);

                if (null === ($_appKey = AppKey::mine($_user->id, OwnerTypes::USER))) {
                    $_appKey = AppKey::create([
                        'key_class_text' => AppKeyClasses::USER,
                        'owner_id'       => $_user->id,
                        'owner_type_nbr' => OwnerTypes::USER,
                        'server_secret'  => config('dfe.security.console-api-key'),
                    ]);
                }

                //  Update the user with the key info and activate
                $_user->api_token_text = $_appKey->client_id;
                $_user->active_ind = 1;
                $_user->save();

                return $_user;
            });

            $_values = $_user->toArray();
            unset($_values['password_text'], $_values['external_password_text']);

            $this->info('new user registered through partner api',
                ['channel' => 'ops.partner', 'user' => $_values]);

            return $_user;
        } catch (\Exception $_ex) {
            if (false !== ($_pos = stripos($_message = $_ex->getMessage(), ' (sql: '))) {
                $_message = substr($_message, 0, $_pos);
            }

            $this->error('database error creating user from partner post: ' . $_message,
                ['channel' => 'ops.partner']);

            throw new RegistrationException($_message, $_ex->getCode(), $_ex);
        }
    }

    /**
     * @param mixed|null $contents
     * @param int|null   $httpCode
     *
     * @return array
     */
    protected function success($contents, $httpCode = Response::HTTP_OK)
    {
        return SuccessPacket::make(true, $contents, $httpCode);
    }

    /**
     * @param int|\Exception $httpCode
     * @param string|null    $message
     * @param mixed|null     $contents
     *
     * @return array
     */
    protected function failure($httpCode, $message = null, $contents = null)
    {
        if ($httpCode instanceof \Exception) {
            $_ex = $httpCode;
            $httpCode = $_ex->getCode();
            !$message && $message = $_ex->getMessage();
        } elseif ($message instanceof \Exception) {
            $_ex = $message;
            !$httpCode && $httpCode = $_ex->getCode();
            $message = $_ex->getMessage();
        }

        return ErrorPacket::make(false, $contents, $httpCode ?: Response::HTTP_NOT_FOUND, $message);
    }
}
