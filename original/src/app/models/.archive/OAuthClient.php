<?php
/**
 * This is the model class for table "oauth_client_t".
 *
 * @property integer $id
 * @property string $client_name_text
 * @property string $client_id_text
 * @property string $client_secret_text
 * @property string $signature_methods_text
 * @property string $server_uri_text
 * @property string $server_uri_host_text
 * @property string $server_uri_path_text
 * @property string $request_token_uri_text
 * @property string $authorize_uri_text
 * @property string $access_token_uri_text
 * @property string $create_date
 * @property string $lmod_date
 *
 * The followings are the available model relations:
 * @property OAuthClientToken[] $tokens
 */
class OAuthClient extends BaseAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return OAuthClient the static model class
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
		return 'oauth_client_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array(
				'client_id_text, client_secret_text, server_uri_text, server_uri_host_text, server_uri_path_text, request_token_uri_text, authorize_uri_text, access_token_uri_text',
				'required'
			),
			array(
				'client_id_text, client_secret_text, server_uri_host_text, server_uri_path_text', 'length',
				'max'=> 128
			),
			array(
				'signature_methods_text, server_uri_text, request_token_uri_text, authorize_uri_text, access_token_uri_text', 'length',
				'max'=> 255
			),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array(
				'id, client_id_text, signature_methods_text, server_uri_text, server_uri_host_text, server_uri_path_text, request_token_uri_text, authorize_uri_text, access_token_uri_text, create_date, lmod_date',
				'safe',
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
			'tokens' => array( self::HAS_MANY, 'OAuthClientToken', 'client_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'client_name_text'       => 'Name',
			'client_id_text'         => 'Client Id',
			'client_secret_text'     => 'Client Secret',
			'signature_methods_text' => 'Signature Methods',
			'server_uri_text'        => 'Server URI',
			'server_uri_host_text'   => 'Server URI Host',
			'server_uri_path_text'   => 'Server URI Path',
			'request_token_uri_text' => 'Request Token URI',
			'authorize_uri_text'     => 'Authorize URI',
			'access_token_uri_text'  => 'Access Token URI',
			'create_date'            => 'Create Date',
			'lmod_date'              => 'Last Modified Date',
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
		$_criteria->compare( 'client_id_text', $this->client_id_text, true );
		$_criteria->compare( 'signature_methods_text', $this->signature_methods_text, true );
		$_criteria->compare( 'server_uri_text', $this->server_uri_text, true );
		$_criteria->compare( 'server_uri_host_text', $this->server_uri_host_text, true );
		$_criteria->compare( 'server_uri_path_text', $this->server_uri_path_text, true );
		$_criteria->compare( 'request_token_uri_text', $this->request_token_uri_text, true );
		$_criteria->compare( 'authorize_uri_text', $this->authorize_uri_text, true );
		$_criteria->compare( 'access_token_uri_text', $this->access_token_uri_text, true );
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