<?php

/**
 * This is the model class for table "auth_token_t".
 *
 * The followings are the available columns in table 'auth_token_t':
 *
 * @property integer   $id
 * @property integer   $owner_id
 * @property integer   $client_id_text
 * @property integer   $refresh_ind
 * @property string    $scope_text
 * @property string    $token_text
 * @property integer   $expire_date
 * @property string    $create_date
 * @property string    $lmod_date
 *
 * The followings are the available model relations:
 * @property AuthOwner $owner
 */
class AuthToken extends BaseAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return AuthToken the static model class
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
		return 'auth_token_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'owner_id, client_id_text, token_text, expire_date, create_date, lmod_date', 'required' ),
			array( 'owner_id, client_id_text, refresh_ind, expire_date', 'numerical', 'integerOnly' => true ),
			array( 'scope_text, token_text', 'length', 'max' => 256 ),
			array( 'id, owner_id, client_id_text, scope_text, token_text, expire_date, create_date, lmod_date', 'safe', 'on' => 'search' ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'owner' => array( self::BELONGS_TO, 'AuthOwner', 'owner_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'             => 'ID',
			'owner_id'       => 'Owner ID',
			'client_id_text' => 'Client ID',
			'scope_text'     => 'Scope',
			'token_text'     => 'Token',
			'expire_date'    => 'Expire Date',
			'create_date'    => 'Create Date',
			'lmod_date'      => 'Lmod Date',
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
		$criteria->compare( 'owner_id', $this->owner_id );
		$criteria->compare( 'client_id_text', $this->client_id_text );
		$criteria->compare( 'scope_text', $this->scope_text, true );
		$criteria->compare( 'token_text', $this->token_text, true );
		$criteria->compare( 'expire_date', $this->expire_date );
		$criteria->compare( 'create_date', $this->create_date, true );
		$criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider( $this, array(
													'criteria' => $criteria,
											   ) );
	}
}