<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;
use Cerberus\Yii\Models\Deploy\Instance;
use DreamFactory\Enums\MailTemplates;
use DreamFactory\Services\CouchDb\WorkQueue;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Sql;

/**
 * This is the model for table "user_t"
 *
 * @property integer       $id
 * @property integer       $drupal_id
 * @property string        $api_token_text
 * @property string        $first_name_text
 * @property string        $last_name_text
 * @property string        $display_name_text
 * @property string        $email_addr_text
 * @property string        $password_text
 * @property string        $drupal_password_text
 * @property integer       $owner_id
 * @property integer       $owner_type_nbr
 * @property string        $company_name_text
 * @property string        $title_text
 * @property string        $city_text
 * @property string        $state_province_text
 * @property string        $country_text
 * @property string        $postal_code_text
 * @property string        $phone_text
 * @property string        $fax_text
 * @property integer       $opt_in_ind
 * @property integer       $agree_ind
 * @property string        $valid_email_hash_text
 * @property integer       $valid_email_hash_expire_time
 * @property string        $valid_email_date
 * @property string        $recover_hash_text
 * @property integer       $recover_hash_expire_time
 * @property string        $last_login_date
 * @property string        $last_login_ip_text
 * @property integer       $admin_ind
 * @property string        $storage_id_text
 * @property integer       $activate_ind Indicates that the user has activated at least one DSP
 * @property string        $create_date
 * @property string        $lmod_date
 *
 * Relations:
 *
 * @property UserConfig[]  $configs
 * @property Instance[]    $instances
 */
class User extends BaseFabricAuthModel implements ConsumerLike
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var string
     */
    const Base64CharacterSet    = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const DRUPAL_MIN_HASH_COUNT = 7;
    const DRUPAL_MAX_HASH_COUNT = 30;
    const DRUPAL_HASH_LENGTH    = 55;

    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var bool
     */
    private $_hashPassword = true;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return User the static model class
     */
    public static function model( $className = __CLASS__ )
    {
        return parent::model( $className );
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'user_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('first_name_text, last_name_text, email_addr_text, password_text, storage_id_text, create_date, lmod_date', 'required'),
            array(
                'drupal_id, owner_id, owner_type_nbr, opt_in_ind, agree_ind, valid_email_hash_expire_time, recover_hash_expire_time, admin_ind',
                'numerical',
                'integerOnly' => true
            ),
            array(
                'api_token_text, display_name_text, company_name_text, title_text, valid_email_hash_text, recover_hash_text',
                'length',
                'max' => 128
            ),
            array('first_name_text, last_name_text, city_text, state_province_text, last_login_ip_text, storage_id_text', 'length', 'max' => 64),
            array('email_addr_text, password_text, drupal_password_text', 'length', 'max' => 200),
            array('country_text', 'length', 'max' => 2),
            array('postal_code_text, phone_text, fax_text', 'length', 'max' => 32),
            array('valid_email_date, last_login_date', 'safe'),
            array(
                'id, drupal_id, api_token_text, first_name_text, last_name_text, display_name_text, email_addr_text, password_text, drupal_password_text, owner_id, owner_type_nbr, company_name_text, title_text, city_text, state_province_text, country_text, postal_code_text, phone_text, fax_text, opt_in_ind, agree_ind, valid_email_hash_text, valid_email_hash_expire_time, valid_email_date, recover_hash_text, recover_hash_expire_time, last_login_date, last_login_ip_text, admin_ind, storage_id_text, create_date, lmod_date',
                'safe',
                'on' => 'search'
            ),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'configs'   => array(static::HAS_MANY, 'Cerberus\\Yii\\Models\\Auth\\UserConfig', 'user_id'),
            'instances' => array(static::HAS_MANY, 'Cerberus\\Yii\\Models\\Deploy\\Instance', 'user_id', 'order' => 'instances.instance_name_text'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'                           => 'ID',
            'drupal_id'                    => 'Drupal',
            'api_token_text'               => 'Api Token',
            'first_name_text'              => 'First Name',
            'last_name_text'               => 'Last Name',
            'display_name_text'            => 'Display Name',
            'email_addr_text'              => 'Email Addr',
            'password_text'                => 'Password',
            'drupal_password_text'         => 'Drupal Password',
            'owner_id'                     => 'Owner',
            'owner_type_nbr'               => 'Owner Type',
            'company_name_text'            => 'Company Name',
            'title_text'                   => 'Title',
            'city_text'                    => 'City',
            'state_province_text'          => 'State Province',
            'country_text'                 => 'Country',
            'postal_code_text'             => 'Postal Code',
            'phone_text'                   => 'Phone',
            'fax_text'                     => 'Fax',
            'opt_in_ind'                   => 'Opt In',
            'agree_ind'                    => 'Agree',
            'valid_email_hash_text'        => 'Valid Email Hash',
            'valid_email_hash_expire_time' => 'Valid Email Hash Expire Time',
            'valid_email_date'             => 'Valid Email Date',
            'recover_hash_text'            => 'Recover Hash',
            'recover_hash_expire_time'     => 'Recover Hash Expire Time',
            'last_login_date'              => 'Last Login Date',
            'last_login_ip_text'           => 'Last Login Ip',
            'admin_ind'                    => 'Admin',
            'storage_id_text'              => 'Storage Id',
            'create_date'                  => 'Create Date',
            'lmod_date'                    => 'Last Modified Date',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @param bool $returnCriteria
     *
     * @return \CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search( $returnCriteria = false )
    {
        $_criteria = new \CDbCriteria();

        $_criteria->compare( 'id', $this->id );
        $_criteria->compare( 'drupal_id', $this->drupal_id );
        $_criteria->compare( 'api_token_text', $this->api_token_text, true );
        $_criteria->compare( 'first_name_text', $this->first_name_text, true );
        $_criteria->compare( 'last_name_text', $this->last_name_text, true );
        $_criteria->compare( 'display_name_text', $this->display_name_text, true );
        $_criteria->compare( 'email_addr_text', $this->email_addr_text, true );
        $_criteria->compare( 'password_text', $this->password_text, true );
        $_criteria->compare( 'drupal_password_text', $this->drupal_password_text, true );
        $_criteria->compare( 'owner_id', $this->owner_id );
        $_criteria->compare( 'owner_type_nbr', $this->owner_type_nbr );
        $_criteria->compare( 'company_name_text', $this->company_name_text, true );
        $_criteria->compare( 'title_text', $this->title_text, true );
        $_criteria->compare( 'city_text', $this->city_text, true );
        $_criteria->compare( 'state_province_text', $this->state_province_text, true );
        $_criteria->compare( 'country_text', $this->country_text, true );
        $_criteria->compare( 'postal_code_text', $this->postal_code_text, true );
        $_criteria->compare( 'phone_text', $this->phone_text, true );
        $_criteria->compare( 'fax_text', $this->fax_text, true );
        $_criteria->compare( 'opt_in_ind', $this->opt_in_ind );
        $_criteria->compare( 'agree_ind', $this->agree_ind );
        $_criteria->compare( 'valid_email_hash_text', $this->valid_email_hash_text, true );
        $_criteria->compare( 'valid_email_hash_expire_time', $this->valid_email_hash_expire_time );
        $_criteria->compare( 'valid_email_date', $this->valid_email_date, true );
        $_criteria->compare( 'recover_hash_text', $this->recover_hash_text, true );
        $_criteria->compare( 'recover_hash_expire_time', $this->recover_hash_expire_time );
        $_criteria->compare( 'last_login_date', $this->last_login_date, true );
        $_criteria->compare( 'last_login_ip_text', $this->last_login_ip_text, true );
        $_criteria->compare( 'admin_ind', $this->admin_ind );
        $_criteria->compare( 'storage_id_text', $this->storage_id_text, true );
        $_criteria->compare( 'create_date', $this->create_date, true );
        $_criteria->compare( 'lmod_date', $this->lmod_date, true );

        if ( $returnCriteria )
        {
            return $_criteria;
        }

        return new \CActiveDataProvider(
            $this, array(
                'criteria' => $_criteria,
            )
        );
    }

    /**
     * Scope to pull by api token or email
     *
     * @param string $token
     * @param string $emailAddress
     * @param int    $drupalId
     *
     * @throws \CDbException
     * @return User
     */
    public function byTokenOrEmailOrDrupalOhMy( $token = null, $emailAddress = null, $drupalId = null )
    {
        $_condition = array();
        $_params = array();

        if ( !empty( $token ) )
        {
            $_params[':api_token_text'] = $token;
            $_condition[] = 'api_token_text = :api_token_text';
        }

        if ( !empty( $emailAddress ) )
        {
            $_params[':email_addr_text'] = $emailAddress;
            $_condition[] = 'email_addr_text = :email_addr_text';
        }

        if ( !empty( $drupalId ) )
        {
            $_params[':drupal_id'] = $drupalId;
            $_condition[] = 'drupal_id = :drupal_id';
        }

        if ( empty( $_condition ) )
        {
            throw new \CDbException( 'Stupid query, try again.' );
        }

        $_condition = implode( ' OR ', $_condition );

        $this->getDbCriteria()->mergeWith(
            array(
                'condition' => $_condition,
                'params'    => $_params
            )
        );

        return $this;
    }

    /**
     * Scope to pull by email
     *
     * @param string $emailAddress
     *
     * @return User
     */
    public function byEmailAddress( $emailAddress )
    {
        $this->getDbCriteria()->mergeWith(
            array(
                'condition' => 'email_addr_text = :email_addr_text',
                'params'    => array(
                    ':email_addr_text' => $emailAddress,
                ),
            )
        );

        return $this;
    }

    /**
     * Scope to pull by recovery hash
     *
     * @param string $hash
     *
     * @return User
     */
    public function byRecoverHash( $hash )
    {
        $this->getDbCriteria()->mergeWith(
            array(
                'condition' => 'recover_hash_text = :recover_hash_text',
                'params'    => array(
                    ':recover_hash_text' => $hash,
                ),
            )
        );

        return $this;
    }

    /**
     * Scope to pull by email confirmation hash
     *
     * @param string $hash
     *
     * @return User
     */
    public function byEmailHash( $hash )
    {
        $this->getDbCriteria()->mergeWith(
            array(
                'condition' => 'valid_email_hash_text = :valid_email_hash_text',
                'params'    => array(
                    ':valid_email_hash_text' => $hash,
                ),
            )
        );

        return $this;
    }

    /**
     * User lookup/authentication
     *
     * @param string $userName
     * @param string $password
     *
     * @throws \CDbException
     * @return bool|int The user ID or false when not found
     */
    public static function authenticate( $userName, $password )
    {
        $userName = trim( strtolower( $userName ) );

        Log::debug( 'auth: ' . $userName . ' ' . $password );

        $_sql
            = <<<SQL
SELECT
	id
FROM
	user_t
WHERE
	(
		email_addr_text = :email_addr_text AND
		password_text = :password_text
	) OR
	api_token_text = :api_token_text
SQL;

        $_userId = Sql::scalar(
            $_sql,
            0,
            array(
                ':email_addr_text' => $userName,
                ':password_text'   => static::generatePasswordHash( $password ),
                ':api_token_text'  => $password
            ),
            Pii::pdo()
        );

        if ( empty( $_userId ) )
        {
            return false;
        }

        /** @var $_user User */
        if ( null === ( $_user = self::model()->findByPk( $_userId ) ) )
        {
            //	User record not found....
            throw new \CDbException( 'Unable to read user record.' );
        }

        Pii::setState(
            'auth_info',
            $_authInfo = array(
                'last_login_date'    => date( 'c' ),
                'last_login_ip_text' => $_SERVER['REMOTE_ADDR'],
                'session_id'         => session_id(),
                'display_name'       => $_user->display_name_text,
                'email_addr_text'    => $_user->email_addr_text,
            )
        );

        Log::info( 'AUTH', $_authInfo );

        return $_user->id;
    }

    /**
     * @param string $newPassword
     *
     * @return bool
     */
    public function resetPassword( $newPassword )
    {
        return $this->update(
            array(
                'password_text' => static::generatePasswordHash( $newPassword )
            )
        );
    }

    /**
     * Hash the password before saving...
     */
    public function beforeSave()
    {
        if ( parent::beforeSave() )
        {
            if ( $this->isNewRecord || isset( $this->new_password_text ) )
            {
                $_password = isset( $this->new_password_text ) ? $this->new_password_text : $this->password_text;
                $this->password_text = $this->_hashPassword ? static::generatePasswordHash( $_password ) : $_password;
            }

            //	Set the display name if not set
            if ( Pii::isEmpty( trim( $this->display_name_text ) ) )
            {
                $this->display_name_text = $this->first_name_text . ' ' . $this->last_name_text;
            }

            return true;
        }
    }

    /**
     * Sends a email verification email to a user
     */
    public function sendEmailConfirmation()
    {
        $this->generateEmailVerificationHash( true );

        $_data = array_merge(
            $this->getAttributes(),
            array(
                'to'   => array($this->email_addr_text => $this->display_name_text),
                'bcc'  => array('developer-central@dreamfactory.com' => '[DFDC] New User Registration'),
                'from' => array('developer-central@dreamfactory.com' => 'DreamFactory Developer Central'),
                'type' => MailTemplates::WelcomeEmail,
            )
        );

        /** @var $_queue WorkQueue */
        if ( null === ( $_queue = \Kisma::get( 'app.work_queue' ) ) )
        {
            $_mailer = new \DreamMailer( $this );
            $_mailer->send( $_data );

            return true;
        }

        $_id = $_queue->enqueue( $_data, 'email' );
        Log::info( 'Email queued: ' . $_id );

        return true;
    }

    /**
     * @return bool
     */
    public function sendPasswordRecovery()
    {
        $this->generatePasswordRecoveryHash( true );

        $_data = array_merge(
            $this->getAttributes(),
            array(
                'to'   => array($this->email_addr_text => $this->display_name_text),
                'bcc'  => array('developer-central@dreamfactory.com' => '[DFDC] Recover Your Password'),
                'from' => array('developer-central@dreamfactory.com' => 'DreamFactory Developer Central'),
                'type' => MailTemplates::PasswordReset,
            )
        );

        /** @var $_queue WorkQueue */
        if ( null === ( $_queue = \Kisma::get( 'app.work_queue' ) ) )
        {
            $_mailer = new \DreamMailer( $this );
            $_mailer->send( $_data );

            return true;
        }

        $_id = $_queue->enqueue( $_data, 'email' );
        Log::info( 'Email queued: ' . $_id );

        return true;
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public static function generatePasswordHash( $password )
    {
        return hash( 'sha512', $password );
    }

    /**
     * @param bool $save
     *
     * @return string
     */
    public function generateEmailVerificationHash( $save = false )
    {
        return $this->_generateHash( 'valid_email', '*', $save );
    }

    /**
     * @param bool $save
     *
     * @return string
     */
    public function generatePasswordRecoveryHash( $save = false )
    {
        return $this->_generateHash( 'recover', '|', $save );
    }

    /**
     * @param string $type Either "valid_email" or "recover"
     * @param string $delimiter
     * @param bool   $save If true, data is saved before return
     *
     * @return string
     */
    protected function _generateHash( $type, $delimiter = '*', $save = true )
    {
        $_text = $type . '_hash_text';
        $_expires = $type . '_expire_time';
        $_date = $type . '_date';

        $this->setAttribute( $_text, sha1( implode( $delimiter, $this->getAttributes() ) . microtime( true ) ) );
        $this->setAttribute( $_expires, time() + \Kisma\Core\Enums\DateTime::SecondsPerDay );

        //	Set optional date
        if ( $this->hasAttribute( $_date ) )
        {
            $this->setAttribute( $_date, null );
        }

        if ( true === $save )
        {
            $this->update( array($_text, $_expires) );
            $this->refresh();
        }

        return $this->{$_text};
    }

    /**
     * Activate a user
     *
     * @param string $hash
     *
     * @return bool
     */
    public static function activate( $hash )
    {
        /** @var $_user User */
        $_user = static::model()->find(
            array(
                'select'    => 'id, valid_email_date',
                'condition' => 'valid_email_hash_text = :valid_email_hash_text',
                'params'    => array(
                    ':valid_email_hash_text' => $hash,
                )
            )
        );

        //	Valid hash?
        if ( null === $_user )
        {
            Log::error( 'Activation of invalid hash received: ' . $hash . '' );

            return false;
        }

        //	Hash is valid. But already confirmed...
        if ( null !== $_user->valid_email_date )
        {
            Pii::controller()->redirect( array('/app/thanks/', 'confirmed' => 1) );
        }

        $_user->update(
            array(
                'valid_email_date' => date( 'c' ),
            )
        );

        return true;
    }

    /**
     * @return bool
     */
    public function sendPasswordChangeConfirmation()
    {
        $this->generatePasswordRecoveryHash( true );

        $_data = array_merge(
            $this->getAttributes(),
            array(
                'to'   => array($this->email_addr_text => $this->display_name_text),
                'bcc'  => array('developer-central@dreamfactory.com' => '[DFDC] Your Password Was Changed'),
                'from' => array('developer-central@dreamfactory.com' => 'DreamFactory Developer Central'),
                'type' => MailTemplates::PasswordChanged,
            )
        );

        /** @var $_queue WorkQueue */
        if ( null === ( $_queue = \Kisma::get( 'app.work_queue' ) ) )
        {
            $_mailer = new \DreamMailer( $this );
            $_mailer->send( $_data );

            return true;
        }

        $_id = $_queue->enqueue( $_data, 'email' );
        Log::info( 'Password Change email queued: ' . $_id );

        return true;
    }

    /**
     * @param boolean $hashPassword
     *
     * @return User
     */
    public function setHashPassword( $hashPassword )
    {
        $this->_hashPassword = $hashPassword;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getHashPassword()
    {
        return $this->_hashPassword;
    }

    /**
     * Check whether a plain text password matches a stored hashed password.
     *
     * @param string $password A plain-text password
     *
     * @return bool
     */
    public function validateDrupalPassword( $password )
    {
        if ( substr( $this->drupal_password_text, 0, 2 ) == 'U$' )
        {
            // This may be an updated password from user_update_7000(). Such hashes
            // have 'U' added as the first character and need an extra md5().
            $_storedHash = substr( $this->drupal_password_text, 1 );
            $password = md5( $password );
        }
        else
        {
            $_storedHash = $this->drupal_password_text;
        }

        switch ( substr( $_storedHash, 0, 3 ) )
        {
            case '$S$':
                // A normal Drupal 7 password using sha512.
                $_hash = $this->_drupalHash( 'sha512', $password, $_storedHash );
                break;

            case '$H$':
                // phpBB3 uses "$H$" for the same thing as "$P$".
            case '$P$':
                // A phpass password generated using md5.  This is an
                // imported password or from an earlier Drupal version.
                $_hash = $this->_drupalHash( 'md5', $password, $_storedHash );
                break;
            default:
                return false;
        }

        return ( $_hash && $_storedHash == $_hash );
    }

    /**
     * Hash a password using a secure stretched hash.
     *
     * By using a salt and repeated hashing the password is "stretched". Its
     * security is increased because it becomes much more computationally costly
     * for an attacker to try to break the hash by brute-force computation of the
     * hashes of a large number of plain-text words or strings to find a match.
     *
     * @param string $hashType The string name of a hashing algorithm usable by hash(), like 'sha256'.
     * @param string $password The plain-text password to hash.
     * @param string $setting  An existing hash or the output of _password_generate_salt().
     *                         Must be at least 12 characters (the settings and salt).
     *
     * @return bool|string A string containing the hashed password (and salt) or FALSE on failure.
     */
    protected function _drupalHash( $hashType, $password, $setting )
    {
        // The first 12 characters of an existing hash are its setting string.
        $setting = substr( $setting, 0, 12 );

        if ( $setting[0] != '$' || $setting[2] != '$' )
        {
            return false;
        }

        $_count = $this->_drupalPasswordGetCountLog2( $setting );

        // Hashes may be imported from elsewhere, so we allow != DRUPAL_HASH_COUNT
        if ( $_count < static::DRUPAL_MIN_HASH_COUNT || $_count > static::DRUPAL_MAX_HASH_COUNT )
        {
            return false;
        }

        $_salt = substr( $setting, 4, 8 );

        // Hashes must have an 8 character salt.
        if ( strlen( $_salt ) != 8 )
        {
            return false;
        }

        // Convert the base 2 logarithm into an integer.
        $_count = 1 << $_count;

        // We rely on the hash() function being available in PHP 5.2+.
        $_hash = hash( $hashType, $_salt . $password, true );

        do
        {
            $_hash = hash( $hashType, $_hash . $password, true );
        }
        while ( --$_count );

        $_len = strlen( $_hash );

        $_output = $setting . $this->_drupalPasswordBase64Encode( $_hash, $_len );

        // _password_base64_encode() of a 16 byte MD5 will always be 22 characters.
        // _password_base64_encode() of a 64 byte sha512 will always be 86 characters.
        $_expected = 12 + ceil( ( 8 * $_len ) / 6 );

        return ( strlen( $_output ) == $_expected ) ? substr( $_output, 0, static::DRUPAL_HASH_LENGTH ) : false;
    }

    /**
     * Parse the log2 iteration count from a stored hash or setting string.
     *
     * @param string $setting
     *
     * @return string
     */
    protected function _drupalPasswordGetCountLog2( $setting )
    {
        return strpos( static::Base64CharacterSet, $setting[3] );
    }

    /**
     * Encode bytes into printable base 64 using the *nix standard from crypt().
     *
     * @param $input
     *   The string containing bytes to encode.
     * @param $count
     *   The number of characters (bytes) to encode.
     *
     * @return string Encoded string
     */
    protected function _drupalPasswordBase64Encode( $input, $count )
    {
        $_output = '';
        $_index = 0;
        $_charSet = static::Base64CharacterSet;

        do
        {
            $_value = ord( $input[$_index++] );
            $_output .= $_charSet[$_value & 0x3f];

            if ( $_index < $count )
            {
                $_value |= ord( $input[$_index] ) << 8;
            }

            $_output .= $_charSet[( $_value >> 6 ) & 0x3f];

            if ( $_index++ >= $count )
            {
                break;
            }

            if ( $_index < $count )
            {
                $_value |= ord( $input[$_index] ) << 16;
            }

            $_output .= $_charSet[( $_value >> 12 ) & 0x3f];

            if ( $_index++ >= $count )
            {
                break;
            }

            $_output .= $_charSet[( $_value >> 18 ) & 0x3f];
        }
        while ( $_index < $count );

        return $_output;
    }

    /**
     * Checks if a user can start a trial
     *
     * @return bool FALSE if user cannot start a trial, otherwise true
     */
    public function inTrial()
    {
        //	Can user launch a trial?
        if ( $this->instances )
        {
            foreach ( $this->instances as $_instance )
            {
                if ( 1 == $_instance->provision_ind && 1 == $_instance->trial_instance_ind )
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array|bool
     */
    public function getTrialInstance()
    {
        //	Can user launch a trial?
        if ( $this->instances )
        {
            foreach ( $this->instances as $_instance )
            {
                if ( 1 == $_instance->provision_ind && 1 == $_instance->trial_instance_ind )
                {
                    return $_instance->getRestAttributes();
                }
            }
        }

        return new \stdClass();
    }

    /**
     * @return \stdClass
     */
    public function asDrupalObject()
    {
        $_object = new \stdClass();
        $_object->uid = $this->drupal_id;
        $_object->mail = $this->email_addr_text;
        $_object->pass = $this->drupal_password_text;
        $_object->name = $this->display_name_text;
        $_object->created = $this->create_date;
        $_object->field_first_name = $this->first_name_text;
        $_object->field_last_name = $this->last_name_text;
        $_object->field_company_name = $this->company_name_text;
        $_object->field_city = $this->city_text;
        $_object->field_state_province = $this->state_province_text;
        $_object->field_zip_postal_code = $this->postal_code_text;
        $_object->field_country = $this->country_text;
        $_object->field_phone_number = $this->phone_text;
        $_object->field_title = $this->title_text;
        $_object->token = $this->api_token_text;

        return $_object;
    }
}