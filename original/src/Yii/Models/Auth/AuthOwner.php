<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "auth_owner_t"
 *
 * @property integer      $id
 * @property string       $profile_id_text
 * @property integer      $devweb_user_id
 * @property integer      $drupal_user_id
 * @property string       $auth_id_text
 * @property string       $email_addr_text
 * @property string       $password_text
 * @property string       $create_date
 * @property string       $lmod_date
 *
 * Relations:
 * @property AuthCodeT[]  $authCodeTs
 * @property AuthTokenT[] $authTokenTs
 */
class AuthOwner extends BaseFabricAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return AuthOwnerT the static model class
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
		return 'auth_owner_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array(
				'profile_id_text, devweb_user_id, drupal_user_id, auth_id_text, email_addr_text, password_text, create_date, lmod_date',
				'required'
			),
			array( 'devweb_user_id, drupal_user_id', 'numerical', 'integerOnly' => true ),
			array( 'profile_id_text, auth_id_text', 'length', 'max' => 128 ),
			array( 'email_addr_text', 'length', 'max' => 300 ),
			array( 'password_text', 'length', 'max' => 200 ),
			array(
				'id, profile_id_text, devweb_user_id, drupal_user_id, auth_id_text, email_addr_text, password_text, create_date, lmod_date',
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
			'authCodes'  => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Auth\\AuthCode', 'owner_id' ),
			'authTokens' => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Auth\\AuthToken', 'owner_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'              => 'ID',
			'profile_id_text' => 'Profile Id',
			'devweb_user_id'  => 'Devweb User',
			'drupal_user_id'  => 'Drupal User',
			'auth_id_text'    => 'Auth Id',
			'email_addr_text' => 'Email Addr',
			'password_text'   => 'Password',
			'create_date'     => 'Create Date',
			'lmod_date'       => 'Last Modified Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new \CDbCriteria();

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'profile_id_text', $this->profile_id_text, true );
		$_criteria->compare( 'devweb_user_id', $this->devweb_user_id );
		$_criteria->compare( 'drupal_user_id', $this->drupal_user_id );
		$_criteria->compare( 'auth_id_text', $this->auth_id_text, true );
		$_criteria->compare( 'email_addr_text', $this->email_addr_text, true );
		$_criteria->compare( 'password_text', $this->password_text, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new \CActiveDataProvider(
			$this,
			array(
				 'criteria' => $_criteria,
			)
		);
	}
}