<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Exceptions\DatabaseException;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Listeners\ProvisionJobHandler;
use DreamFactory\Library\Utility\Curl;
use DreamFactory\Library\Utility\Uri;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FastTrackController extends FactoryController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return View
     */
    public function index()
    {
        return view('fast-track.main');
    }

    /**
     * Receives a post and performs an auto-registration
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function autoRegister(Request $request)
    {
        if (!config('dfe.enable-fast-track', false)) {
            throw new NotFoundHttpException();
        }

        $_input = $request->input();
        $_guid = array_get($_input, 'submissionGuid');

        //  Validate that it came from a HubSpot landing page
        if (config('dfe.fast-track-hubspot-only', false) && empty($_guid)) {
            \Log::error('[dfe.fast-track.auto-register] invalid inbound request', $_input);

            return ErrorPacket::create(null, Response::HTTP_FORBIDDEN);
        }

        //  1.  Validate and create a dashboard user
        try {
            $_user = $this->createDashboardUser($request);
        } catch (\Exception $_ex) {
            return ErrorPacket::create($_input, Response::HTTP_BAD_REQUEST, $_ex);
        }

        $_response = [
            'user'        => $_user->toArray(),
            'instance-id' => false,
            'instance'    => $_instance = false,
        ];

        //  2.  Generate an instance name for this dude
        $_instanceId = $this->autoNameInstance($_input);

        //  3.  Create an instance
        try {
            $_instance = ProvisionJobHandler::provision([
                'owner-id'       => $_user->id,
                'instance-id'    => $_instanceId,
                'guest-location' => array_get($_input, 'guest-location', GuestLocations::DFE_CLUSTER),
            ]);

            if (empty($_instance)) {
                throw new ProvisioningException('Instance provisioning failed.');
            }

            $_response['instance-id'] = $_instanceId;
            $_response['instance'] = $_instance->toArray();

            \Log::info('[dfe.fast-track.auto-register] instance created - ' . ($_endpoint = $_instance->getProvisionedEndpoint()));
        } catch (\Exception $_ex) {
            \Log::error('[dfe.fast-track.auto-register] exception: ' . $_ex->getMessage());

            //  Return partial success...
            return ErrorPacket::create($_response);
        }

        //  3.  Simulate login to instance to initialize
        $_result = Curl::get($_endpoint . '/', [], [CURLOPT_HTTPHEADER => ['Host: ' . $_instance->getProvisionedEndpoint(false)]]);

        if (false === $_result) {
            \Log::info('[dfe.fast-track.auto-register] partial success - instance init failure');

            //  Return partial success...
            return SuccessPacket::create($_response, Response::HTTP_PARTIAL_CONTENT);
        }

        //  4.  Construct first admin form post from response
        $_token = substr($_result, stripos($_result, 'name="_token"') - 20, 100);

        //  4.  Send notification
        //  5.  Redirect with auto-login to new instance

        return \Redirect::to($_endpoint . '?submissionGuid=' . $_guid);
    }

    /**
     * @param User $user
     *
     * @return bool|string
     */
    protected function generateNameForUser($user)
    {
        $_name = $_check = substr($user->email_addr_text, 0, strpos($user->email_addr_text, '@'));

        while (true) {
            if (false !== ($_check = Instance::isNameAvailable($_check))) {
                break;
            }

            $_check = $_name . rand(333, 999);
        }

        return $_check;
    }

    /**
     * Creates a dashboard user
     *
     * @param Request $request
     *
     * @return \DreamFactory\Enterprise\Database\Models\User
     * @throws \DreamFactory\Enterprise\Database\Exceptions\DatabaseException
     */
    protected function createDashboardUser(Request $request)
    {
        $_input = $request->input();

        if (false === ($_email = filter_var(array_get($_input, 'email', array_get($_input, 'email-address')), FILTER_SANITIZE_EMAIL)) || empty($_email)) {
            \Log::error('[dfe.fast-track.auto-register] invalid email address', $_input);
            throw new \InvalidArgumentException('Invalid email address.');
        }

        /**
         * @type User  $_user
         * @type array $_result
         */
        $_result = User::register($request);
        $_user = array_get($_result, 'response');

        if (empty($_user)) {
            throw new DatabaseException('Unable to create new user.');
        }

        \Log::info('[dfe.fast-track.auto-register] user created - ' . $_user->email_addr_text);

        return $_user;
    }

    protected function autoNameInstance($input, $user)
    {
        if (null === ($_instanceId = array_get($input, 'instance-id'))) {
            if (false === ($_instanceId = $this->generateNameForUser($user))) {
                \Log::info('[dfe.fast-track.auto-register] partial success - instance name unavailable');

                return false;
            }
        }
    }
}
