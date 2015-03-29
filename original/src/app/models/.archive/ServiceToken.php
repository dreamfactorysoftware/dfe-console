<?php
/**
 * This is the model class for table "service_token_t".
 *
 * @property integer      $id
 * @property integer      $service_id
 * @property integer      $user_id
 * @property string       $token_text
 * @property string       $token_secret_text
 * @property string       $refresh_token_text
 * @property string       $expire_date
 * @property string       $issue_date
 * @property string       $create_date
 * @property string       $lmod_date
 *
 * The followings are the available model relations:
 * @property ServiceT     $service
 * @property ServiceUserT $user
 */
class ServiceToken extends BaseModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ServiceToken the static model class
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
		return 'service_token_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'service_id', 'required' ),
			array( 'service_id, user_id', 'numerical', 'integerOnly'=> true ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'service' => array( self::BELONGS_TO, 'Service', 'service_id' ),
			'user'    => array( self::BELONGS_TO, 'ServiceUser', 'user_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                 => 'ID',
			'service_id'         => 'Service ID',
			'user_id'            => 'User ID',
			'token_text'         => 'Token',
			'token_secret_text'  => 'Token Secret',
			'refresh_token_text' => 'Refresh Token',
			'expire_date'        => 'Expiration Date',
			'issue_date'         => 'Issue Date',
			'create_date'        => 'Create Date',
			'lmod_date'          => 'Modified Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria;

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'service_id', $this->service_id );
		$_criteria->compare( 'user_id', $this->user_id );
		$_criteria->compare( 'token_text', $this->token_text, true );
		$_criteria->compare( 'token_secret_text', $this->token_secret_text, true );
		$_criteria->compare( 'refresh_token_text', $this->refresh_token_text, true );
		$_criteria->compare( 'expire_date', $this->expire_date, true );
		$_criteria->compare( 'issue_date', $this->issue_date, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider( $this, array(
				'criteria'=> $_criteria,
			)
		);
	}
}