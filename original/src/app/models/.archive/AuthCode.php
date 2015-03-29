<?php

/**
 * This is the model class for table "auth_code_t".
 *
 * The followings are the available columns in table 'auth_code_t':
 *
 * @property integer   $id
 * @property integer   $owner_id
 * @property string    $code_text
 * @property string    $scope_text
 * @property string    $client_id_text
 * @property string    $redirect_uri_text
 * @property integer   $expire_date
 * @property string    $create_date
 * @property string    $lmod_date
 *
 * The followings are the available model relations:
 * @property AuthOwner $owner
 */
class AuthCode extends BaseAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return AuthCode the static model class
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
		return 'auth_code_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array( 'owner_id, code_text, client_id_text, redirect_uri_text, expire_date, create_date, lmod_date', 'required' ),
			array( 'owner_id, expire_date', 'numerical', 'integerOnly' => true ),
			array( 'code_text', 'length', 'max' => 64 ),
			array( 'scope_text, client_id_text, redirect_uri_text', 'length', 'max' => 256 ),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array(
				'id, owner_id, code_text, scope_text, client_id_text, redirect_uri_text, expire_date, create_date, lmod_date',
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
			'id'                => 'ID',
			'owner_id'          => 'Owner',
			'code_text'         => 'Code Text',
			'scope_text'        => 'Scope Text',
			'client_id_text'    => 'Client Id Text',
			'redirect_uri_text' => 'Redirect Uri Text',
			'expire_date'       => 'Expire Date',
			'create_date'       => 'Create Date',
			'lmod_date'         => 'Lmod Date',
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
		$criteria->compare( 'code_text', $this->code_text, true );
		$criteria->compare( 'scope_text', $this->scope_text, true );
		$criteria->compare( 'client_id_text', $this->client_id_text, true );
		$criteria->compare( 'redirect_uri_text', $this->redirect_uri_text, true );
		$criteria->compare( 'expire_date', $this->expire_date );
		$criteria->compare( 'create_date', $this->create_date, true );
		$criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider( $this, array(
			'criteria' => $criteria,
		) );
	}
}