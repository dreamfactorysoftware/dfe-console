<?php namespace DreamFactory\Enterprise\Console;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use Authenticatable, CanResetPassword;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'service_user_t';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name_text', 'last_name_text', 'email_addr_text', 'password_text'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password_text', 'remember_ind'];

}
