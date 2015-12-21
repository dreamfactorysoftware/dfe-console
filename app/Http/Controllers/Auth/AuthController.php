<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Auth;

use DreamFactory\Enterprise\Common\Http\Controllers\Auth\CommonAuthController;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Facades\License;

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
                'email_addr_text' => 'required|email|max:320|unique:service_user_t,email_addr_text',
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

        return $_serviceUser;
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\ServiceUser $serviceUser
     *
     * @return bool
     */
    protected function postRegistration(ServiceUser $serviceUser)
    {
        try {
            License::registerAdmin($serviceUser);
        } catch (\Exception $_ex) {
            \Log::error('[auth.register] Exception posting registration data to endpoint: ' . $_ex->getMessage());
        }

        return false;
    }
}
