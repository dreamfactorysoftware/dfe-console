<?php namespace DreamFactory\Enterprise\Console;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;

/**
 * DreamFactory\Enterprise\Console\User
 *
 * @property integer $id 
 * @property string $first_name_text 
 * @property string $last_name_text 
 * @property string $display_name_text 
 * @property string $email_addr_text 
 * @property string $password_text Big cuz it is a hash
 * @property integer $owner_id 
 * @property integer $owner_type_nbr 
 * @property string $remember_token 
 * @property string $last_login_date 
 * @property string $last_login_ip_text 
 * @property string $create_date 
 * @property string $lmod_date 
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereFirstNameText($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereLastNameText($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereDisplayNameText($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereEmailAddrText($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User wherePasswordText($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereOwnerId($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereOwnerTypeNbr($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereLastLoginDate($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereLastLoginIpText($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereCreateDate($value)
 * @method static \Illuminate\Database\Query\Builder|\DreamFactory\Enterprise\Console\User whereLmodDate($value)
 */
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
