<?php
namespace Cerberus\Yii\Models\Drupal;

use Cerberus\Yii\Models\Auth\User;
use Cerberus\Yii\Models\BaseDrupalModel;

/**
 * This is the model for table "user_t"
 *
 * @property int           $uid
 * @property string        $name
 * @property string        $pass
 * @property string        $mail
 * @property string        $theme
 * @property string        $signature
 * @property string        $signature_format
 * @property int           $created
 * @property int           $access
 * @property int           $login
 * @property int           $status
 * @property string        $timezone
 * @property string        $language
 * @property int           $picture
 * @property string        $init
 * @property string        $data
 */
class Users extends BaseDrupalModel
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return Users the static model class
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
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'name, uid, mail', 'required' ),
			array( '*', 'safe' ),
		);
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
				 'condition' => 'mail = :mail',
				 'params'    => array(
					 ':mail' => $emailAddress,
				 ),
			 )
		);

		return $this;
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