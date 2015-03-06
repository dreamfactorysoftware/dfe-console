<?php namespace DreamFactory\Enterprise\Common\Http\Controllers\Auth;

use DreamFactory\Enterprise\Common\Http\Controllers\BaseController;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;

class PasswordController extends BaseController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use ResetsPasswords;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new password controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard          $auth
     * @param  \Illuminate\Contracts\Auth\PasswordBroker $passwords
     */
    public function __construct( Guard $auth, PasswordBroker $passwords )
    {
        $this->auth = $auth;
        $this->passwords = $passwords;

        $this->middleware( 'guest' );
    }
}
