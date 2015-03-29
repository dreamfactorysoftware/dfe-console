<?php
/**
 * This is the model class for table "oauth_client_token_t".
 *
 * @property integer $id
 * @property integer $client_id
 * @property string $token_name_text
 * @property string $token_text
 * @property string $token_secret_text
 * @property integer $token_type_nbr
 * @property string $token_ttl_date
 * @property string $create_date
 * @property string $lmod_date
 *
 * The followings are the available model relations:
 * @property OAuthClient $client
 */
class OAuthClientToken extends BaseAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return OAuthClientToken the static model class
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
		return 'oauth_client_token_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'client_id, token_text, token_secret_text', 'required' ),
			array(
				'client_id, token_type_nbr', 'numerical',
				'integerOnly'=> true
			),
			array(
				'token_name_text, token_text, token_secret_text', 'length',
				'max'=> 64
			),
			array(
				'token_ttl_date',
				'safe'
			),

			array(
				'id, client_id, token_name_text, token_text, token_type_nbr, token_ttl_date, create_date, lmod_date', 'safe',
				'on'=> 'search'
			),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'client' => array( self::BELONGS_TO, 'OAuthClient', 'client_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                => 'ID',
			'client_id'         => 'Client',
			'token_name_text'   => 'Token Name',
			'token_text'        => 'Token',
			'token_secret_text' => 'Token Secret',
			'token_type_nbr'    => 'Token Type',
			'token_ttl_date'    => 'Token Expiration',
			'create_date'       => 'Create Date',
			'lmod_date'         => 'Last Modified Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria();

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'client_id', $this->client_id );
		$_criteria->compare( 'token_name_text', $this->token_name_text, true );
		$_criteria->compare( 'token_text', $this->token_text, true );
		$_criteria->compare( 'token_secret_text', $this->token_secret_text, true );
		$_criteria->compare( 'token_type_nbr', $this->token_type_nbr );
		$_criteria->compare( 'token_ttl_date', $this->token_ttl_date, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider(
			$this,
			array(
				'criteria'=> $_criteria,
			)
		);
	}
}