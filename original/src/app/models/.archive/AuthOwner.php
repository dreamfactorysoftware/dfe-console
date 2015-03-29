<?php

/**
 * This is the model class for table "auth_owner_t".
 *
 * The followings are the available columns in table 'auth_owner_t':
 *
 * @property integer             $id
 * @property string              $auth_id_text
 * @property string              $email_addr_text
 * @property string              $password_text
 * @property string              $create_date
 * @property string              $lmod_date
 *
 * The followings are the available model relations:
 * @property AuthClient[]        $authClients
 * @property AuthCode[]          $authCodes
 * @property AuthRefreshToken[]  $authRefreshTokens
 * @property AuthToken[]         $authTokens
 */
class AuthOwner extends BaseAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return AuthOwner the static model class
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
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array( 'auth_id_text, email_addr_text, password_text, create_date, lmod_date', 'required' ),
			array( 'auth_id_text', 'length', 'max' => 128 ),
			array( 'email_addr_text', 'length', 'max' => 300 ),
			array( 'password_text', 'length', 'max' => 200 ),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array( 'id, auth_id_text, email_addr_text, password_text, create_date, lmod_date', 'safe', 'on' => 'search' ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'authClients'       => array( self::HAS_MANY, 'AuthClient', 'owner_id' ),
			'authCodes'         => array( self::HAS_MANY, 'AuthCode', 'owner_id' ),
			'authRefreshTokens' => array( self::HAS_MANY, 'AuthRefreshToken', 'owner_id' ),
			'authTokens'        => array( self::HAS_MANY, 'AuthToken', 'owner_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'              => 'ID',
			'auth_id_text'    => 'Auth Id Text',
			'email_addr_text' => 'Email Addr Text',
			'password_text'   => 'Password Text',
			'create_date'     => 'Create Date',
			'lmod_date'       => 'Lmod Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare( 'id', $this->id );
		$criteria->compare( 'auth_id_text', $this->auth_id_text, true );
		$criteria->compare( 'email_addr_text', $this->email_addr_text, true );
		$criteria->compare( 'password_text', $this->password_text, true );
		$criteria->compare( 'create_date', $this->create_date, true );
		$criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider( $this, array(
			'criteria' => $criteria,
		) );
	}
}