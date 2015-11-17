<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Auth;

use DreamFactory\Enterprise\Common\Http\Controllers\Auth\CommonAuthController;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Library\Utility\Curl;
use Illuminate\Http\Response;

class AuthController extends CommonAuthController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        \Log::debug('validator called with ' . print_r($data, true));

        return \Validator::make($data,
            [
                'first_name_text' => 'required|max:64',
                'last_name_text'  => 'required|max:64',
                'nickname_text'   => 'required|max:64',
                'email_addr_text' => 'required|email|max:320|unique:service_user_t',
                'password_text'   => 'required|confirmed|min:6',
            ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     *
     * @return User
     */
    public function create(array $data)
    {
        $_serviceUser = ServiceUser::create([
            'first_name_text' => array_get($data, 'first_name_text'),
            'last_name_text'  => array_get($data, 'last_name_text'),
            'email_addr_text' => array_get($data, 'email_addr_text'),
            'nickname_text'   => array_get($data, 'nick_name_text'),
            'password_text'   => \Hash::make(array_get($data, 'password_text')),
        ]);

        //  If this is the first registered user, post registration
        if (1 == ServiceUser::count()) {
            $this->postRegistration($_serviceUser);
        }
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\ServiceUser $serviceUser
     *
     * @return bool
     */
    protected function postRegistration(ServiceUser $serviceUser)
    {
        //  Find out post url...
        $_links = config('links.console', []);
        $_url = null;

        foreach ($_links as $_link) {
            if ('first_user' == array_get($_link, 'name') && !array_get($_link, 'show', false)) {
                $_url = $_link['href'];
                break;
            }
        }

        if (empty($_url)) {
            \Log::debug('[auth.register] No registration url found. No registration performed.');

            return false;
        }

        $_payload = $serviceUser->toArray();
        /** @noinspection PhpUndefinedMethodInspection */
        $_payload['install-key'] = UsageServiceProvider::service()->generateInstallKey();

        try {
            if (false !== Curl::post($_url, $_payload)) {
                return Response::HTTP_OK == Curl::getLastHttpCode();
            }

            \Log::error('[auth.register] Network error posting registration data to endpoint.');
        } catch (\Exception $_ex) {
            \Log::error('[auth.register] Exception posting registration data to endpoint: ' . $_ex->getMessage());
        }

        return false;
    }
}
