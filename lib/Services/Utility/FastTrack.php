<?php namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Exceptions\DatabaseException;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Listeners\ProvisionJobHandler;
use DreamFactory\Library\Utility\Curl;
use Illuminate\Http\Request;
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
            'instance-initialized' => false,
            'instance'             => $_instance = false,
            'notification-sent'    => false,
        ];

        //  3.  Generate an instance name for this dude
        $_instanceId = static::generateInstanceName($request->input('instance-id', $_user->email_addr_text));

        //  4.  Create an instance
        if (false === ($_instance = static::createInstance($_user, $_instanceId, $request->input('guest-location', GuestLocations::DFE_CLUSTER)))) {
            //  Return partial success...
            return $_response;
        }

        $_response['instance'] = $_instance;
        $_response['instance-id'] = $_instanceId;

        //  5.  Initialize instance
        if (false === static::initializeInstance($_instance)) {
            //  Something's goofed
            return $_response;
        }

        $_response['instance-initialized'] = true;

        //  6.  Send notification
        $_response['notification-sent'] = false;

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
        if (config('dfe.fast-track.validate-hubspot', false) && empty($request->input('submissionGuid'))) {
            return false;
        }

        return true;
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\User $user
     * @param string                                        $instanceId
     *
     * @return bool|\DreamFactory\Enterprise\Database\Models\Instance
     */
    protected static function createInstance(User $user, $instanceId, $guestLocation = GuestLocations::DFE_CLUSTER)
    {
        try {
            $_instance = ProvisionJobHandler::provision([
                'owner-id'       => $user->id,
                'instance-id'    => $instanceId,
                'guest-location' => $guestLocation,
            ]);

            if (empty($_instance)) {
                throw new ProvisioningException('Instance provisioning failed.');
            }

            \Log::info('[dfe.fast-track.create-instance] instance created - ' . $instanceId);

            return $_instance;
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
        if (false === ($_result = Curl::get($instance->getProvisionedEndpoint()))) {
            \Log::error('[dfe.fast-track.auto-register] unable to initialize new instance');

            return false;
        }

        //  Construct first admin form post from response
        $_token = substr($_result, stripos($_result, 'name="_token"') - 20, 100);

        return true;
    }
}
