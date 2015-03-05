<?php namespace DreamFactory\Enterprise\Console\Services;

use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;
use Validator;

class InstanceRegistrar implements RegistrarContract
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
    public function validator( array $data )
    {
        return Validator::make(
            $data,
            [
                'first_name_text' => 'required|max:60',
                'last_name_text'  => 'required|max:60',
                'email_addr_text' => 'required|email|max:255|unique:user_t',
                'password_text'   => 'required|confirmed|min:6',
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     *
     * @return User
     */
    public function create( array $data )
    {
        return User::create(
            [
                'first_name_text' => $data['first_name_text'],
                'last_name_text'  => $data['last_name_text'],
                'email_addr_text' => $data['email_addr_text'],
                'password_text'   => $data['password_text'],
            ]
        );
    }

}
