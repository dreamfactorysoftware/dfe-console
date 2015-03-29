<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Sql;

/**
 * This is the model for table "service_user_t"
 *
 * @property integer           $id
 * @property string            $first_name_text
 * @property string            $last_name_text
 * @property string            $display_name_text
 * @property string            $email_addr_text
 * @property string            $password_text
 * @property integer           $owner_id
 * @property integer           $owner_type_nbr
 * @property string            $last_login_date
 * @property string            $last_login_ip_text
 * @property string            $create_date
 * @property string            $lmod_date
 *
 * Relations:
 * @property ServiceToken[]    $serviceTokens
 * @property ServiceUserMap[]  $serviceUserMaps
 * @property ServiceUser       $owner
 * @property ServiceUser[]     $serviceUsers
 */
class ServiceUser extends BaseFabricAuthModel
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return ServiceUser the static model class
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
        return 'service_user_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('first_name_text, last_name_text, email_addr_text, password_text, create_date, lmod_date', 'required'),
            array('owner_id, owner_type_nbr', 'numerical', 'integerOnly' => true),
            array('first_name_text, last_name_text, last_login_ip_text', 'length', 'max' => 64),
            array('display_name_text', 'length', 'max' => 128),
            array('email_addr_text', 'length', 'max' => 320),
            array('password_text', 'length', 'max' => 200),
            array('last_login_date', 'safe'),
            array(
                'id, first_name_text, last_name_text, display_name_text, email_addr_text, password_text, owner_id, owner_type_nbr, last_login_date, last_login_ip_text, create_date, lmod_date',
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
            'serviceTokens'   => array(static::HAS_MANY, __NAMESPACE__ . '\\ServiceToken', 'user_id'),
            'serviceUserMaps' => array(static::HAS_MANY, __NAMESPACE__ . '\\ServiceUserMap', 'user_id'),
            'owner'           => array(static::BELONGS_TO, __NAMESPACE__ . '\\ServiceUser', 'owner_id'),
            'serviceUsers'    => array(static::HAS_MANY, __NAMESPACE__ . '\\ServiceUser', 'owner_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'                 => 'ID',
            'first_name_text'    => 'First Name',
            'last_name_text'     => 'Last Name',
            'display_name_text'  => 'Display Name',
            'email_addr_text'    => 'Email Addr',
            'password_text'      => 'Password',
            'owner_id'           => 'Owner',
            'owner_type_nbr'     => 'Owner Type',
            'last_login_date'    => 'Last Login Date',
            'last_login_ip_text' => 'Last Login Ip',
            'create_date'        => 'Create Date',
            'lmod_date'          => 'Last Modified Date',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @param bool $returnCriteria
     *
     * @return bool the data provider that can return the models based on the search/filter conditions.
     */
    public function search( $returnCriteria = false )
    {
        $_criteria = new \CDbCriteria();

        $_criteria->compare( 'id', $this->id );
        $_criteria->compare( 'first_name_text', $this->first_name_text, true );
        $_criteria->compare( 'last_name_text', $this->last_name_text, true );
        $_criteria->compare( 'display_name_text', $this->display_name_text, true );
        $_criteria->compare( 'email_addr_text', $this->email_addr_text, true );
        $_criteria->compare( 'password_text', $this->password_text, true );
        $_criteria->compare( 'owner_id', $this->owner_id );
        $_criteria->compare( 'owner_type_nbr', $this->owner_type_nbr );
        $_criteria->compare( 'last_login_date', $this->last_login_date, true );
        $_criteria->compare( 'last_login_ip_text', $this->last_login_ip_text, true );
        $_criteria->compare( 'create_date', $this->create_date, true );
        $_criteria->compare( 'lmod_date', $this->lmod_date, true );

        if ( false !== $returnCriteria )
        {
            return $_criteria;
        }

        return new \CActiveDataProvider(
            $this,
            array(
                'criteria' => $_criteria,
            )
        );
    }

    /**
     * User lookup/authentication
     *
     * @param string $userName
     * @param string $password
     *
     * @return bool|int The user ID or false when not found
     */
    public static function authenticate( $userName, $password )
    {
        $userName = trim( strtolower( $userName ) );

        $_sql = <<<SQL
SELECT
	id
FROM
	fabric_auth.service_user_t
WHERE
	email_addr_text = :email_addr_text AND
	password_text = sha2(:password_text,512)
SQL;

        $_userId = Sql::scalar(
            $_sql,
            0,
            array(
                ':email_addr_text' => $userName,
                ':password_text'   => $password,
            ),
            Pii::pdo( 'db.fabric_auth' )
        );

        if ( empty( $_userId ) )
        {
            return false;
        }

        $_result = ServiceUser::model()->updateByPk(
            $_userId,
            array(
                'last_login_date'    => date( 'c' ),
                'last_login_ip_text' => $_SERVER['REMOTE_ADDR'],
            )
        );

        if ( empty( $_result ) )
        {
            Log::warning( 'Error updating last_login information in ServiceUser record.' );
        }

        Log::debug( 'User login: ' . $userName );

        return $_userId;
    }
}