<?php namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Exceptions\DatabaseException;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Library\Utility\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FastTrack
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Receives a post and performs an auto-registration
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public static function register(Request $request)
    {
        if (!config('dfe.enable-fast-track', false)) {
            \Log::error('[dfe.fast-track.register] not enabled.');

            throw new NotFoundHttpException();
        }

        //  1. Check request for hubspot, if required
        if (!static::validateHubspot($request)) {
            \Log::error('[dfe.fast-track.auto-register] hubspot required, no submissionGuid found', $request->input());

            throw new NotFoundHttpException();
        }

        //  2.  Validate and create a dashboard user
        if (false === ($_user = static::createDashboardUser($request))) {
            throw new BadRequestHttpException();
        }

        $_response = [
            'user'                 => $_user->toArray(),
            'instance-id'          => false,
            'instance'             => $_instance = false,
            'instance-initialized' => false,
            'instance-admin'       => false,
        ];

        //  3.  Generate an instance name for this dude
        $_instanceId = static::generateInstanceName($request->input('instance-id', $_user->email_addr_text));

        //  4.  Create an instance
        if (false === ($_instance = static::createInstance($_user, $_instanceId, $request->input('guest-location', GuestLocations::DFE_CLUSTER)))) {
            //  Return partial success...
            return $_response;
        }

        $_response['instance'] = $_instance->toArray();
        $_response['instance-id'] = $_instanceId;

        //  5.  Initialize instance
        if (false === static::initializeInstance($_instance)) {
            //  Something's goofed
            return $_response;
        }

        $_response['instance-initialized'] = true;

        //  6.  Create first admin user
        if (false === ($_admin = static::createInstanceAdmin($_user, $_instance, $request))) {
            //  Something's goofed
            return $_response;
        }

        $_response['instance-admin'] = $_admin;

        //  7.  Redirect

        return $_response;
    }

    /**
     * Creates a dashboard user
     *
     * @param Request $request
     *
     * @return bool|\DreamFactory\Enterprise\Database\Models\User
     */
    protected static function createDashboardUser(Request $request)
    {
        try {
            $_input = $request->input();

            if (false === ($_email = filter_var(array_get($_input, 'email', array_get($_input, 'email-address')), FILTER_SANITIZE_EMAIL)) || empty($_email)) {
                \Log::error('[dfe.fast-track.create-dashboard-user] invalid email address', $_input);
                throw new \InvalidArgumentException('Invalid email address.');
            }

            $_result = User::register($request);
            $_user = array_get($_result, 'response');

            if (empty($_user)) {
                throw new DatabaseException('Unable to create new user.');
            }

            \Log::info('[dfe.fast-track.create-dashboard-user] user created - ' . $_user->email_addr_text);

            return $_user;
        } catch (\Exception $_ex) {
            \Log::error('[dfe.fast-track.create-dashboard-user] exception: ' . $_ex->getMessage());
        }

        return false;
    }

    /**
     * Generate an instance name
     *
     * @param string $seed A string upon which to base the name
     *
     * @return bool|string
     */
    protected static function generateInstanceName($seed)
    {
        //  Strip off anything past '@'
        $_name = $_check = substr($seed, 0, strpos($seed, '@'));

        while (true) {
            if (false !== ($_check = Instance::isNameAvailable($_check))) {
                break;
            }

            $_check = $_name . rand(333, 999);
        }

        return $_check;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected static function validateHubspot(Request $request)
    {
        //  Validate that it came from a HubSpot landing page
        if (config('dfe.fast-track-hubspot-only', false) && empty($request->input('submissionGuid'))) {
            return false;
        }

        return true;
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\User $user
     * @param string                                        $instanceId
     * @param int                                           $guestLocation
     *
     * @return bool|\DreamFactory\Enterprise\Database\Models\Instance
     */
    protected static function createInstance(User $user, $instanceId, $guestLocation = GuestLocations::DFE_CLUSTER)
    {
        try {
            $_response = Curl::post(config('app.url') . '/ops/api/v1/provision-ft',
                [
                    'owner-id'       => $user->id,
                    'owner-type'     => OwnerTypes::USER,
                    'instance-id'    => $instanceId,
                    'guest-location' => $guestLocation,
                ]);

            if (!$_response || !$_response->success) {
                throw new ProvisioningException('failure during provisioning process');
            }

            \Log::info('[dfe.fast-track.create-instance] instance created - ' . $instanceId);

            return Instance::byNameOrId($instanceId)->firstOrFail();
        } catch (\Exception $_ex) {
            \Log::error('[dfe.fast-track.create-instance] exception: ' . $_ex->getMessage());
        }

        return false;
    }

    /**
     * @param Instance $instance
     *
     * @return bool
     */
    protected static function initializeInstance(Instance $instance)
    {
        $_host = str_ireplace(['http://', 'https://'], null, $_endpoint = $instance->getProvisionedEndpoint());

        if (false === ($_result = Curl::get($instance->getProvisionedEndpoint(),
                [],
                [CURLOPT_HTTPHEADER => ['Host: ' . $_host, 'User-Agent' => config('dfe.user-agent', 'DreamFactory Enterprise/1.x')]]))
        ) {
            \Log::error('[dfe.fast-track.auto-register] unable to initialize new instance - network error',
                ['endpoint' => $_endpoint, 'host' => $_host, 'info' => Curl::getInfo()]);

            return false;
        }

        if (Response::HTTP_OK != Curl::getLastHttpCode()) {
            \Log::error('[dfe.fast-track.auto-register] unable to initialize new instance - error response',
                ['endpoint' => $_endpoint, 'host' => $_host, 'info' => Curl::getInfo()]);

            return false;
        }

        if (false === stripos($_result, 'DreamFactory Software')) {
            \Log::error('[dfe.fast-track.auto-register] unable to initialize new instance - unrecognized page',
                ['endpoint' => $_endpoint, 'host' => $_host, 'info' => Curl::getInfo()]);

            return false;
        }

        return static::waitForInstanceInitialization($instance);
    }

    /**
     * Wait for up to two minutes for an instance to initialize
     *
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return bool
     */
    protected static function waitForInstanceInitialization(Instance $instance)
    {
        $_db = $instance->instanceConnection($instance);
        $_counter = 0;

        logger('[dfe.fast-track.wait-for-instance-initialization] waiting for instance to finish initialization');

        while (true) {
            $_row = $_db->select('SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :table_schema',
                [':table_schema' => $instance->db_name_text]);

            if (!empty($_row) && data_get($_row, 'table_count', 0) > 40) {
                logger('[dfe.fast-track.wait-for-instance-initialization] instance initialization completed in ' . ($_counter * 10) . ' seconds');

                return true;
            }

            if (++$_counter >= 6) {
                \Log::error('[dfe.fast-track.wait-for-instance-initialization] instance initialization not complete after 60 seconds');

                return false;
            }

            logger('[dfe.fast-track.wait-for-instance-initialization] ** waiting ' . $_counter);
            sleep(10);
        }
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\User     $user
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     * @param \Illuminate\Http\Request                          $request
     *
     * @return bool|\stdClass
     */
    protected static function createInstanceAdmin(User $user, Instance $instance, Request $request)
    {
        $_payload = [
            'email'                 => $user->email_addr_text,
            'password'              => $request->input('password'),
            'password_confirmation' => $request->input('password'),
            'first_name'            => $user->first_name_text,
            'last_name'             => $user->last_name_text,
            'name'                  => $user->nickname_text ?: $user->first_name_text,
        ];

        $_curlOptions = [CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],];

        if (false === ($_response = $instance->call('/setup', $_payload, $_curlOptions))) {
            \Log::error('[dfe.fast-track.create-instance-admin] creation of instance admin failed.');

            return false;
        }

        return $_response;
    }
}
