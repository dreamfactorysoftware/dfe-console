<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Auth;

use DreamFactory\Enterprise\Common\Http\Controllers\Auth\CommonAuthController;
use DreamFactory\Enterprise\Database\Models\ServiceUser;

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
        return ServiceUser::create([
            'first_name_text' => $data['first_name_text'],
            'last_name_text'  => $data['last_name_text'],
            'email_addr_text' => $data['email_addr_text'],
            'password_text'   => bcrypt($data['password_text']),
        ]);
    }
}
