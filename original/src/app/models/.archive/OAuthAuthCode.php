<?php
/**
 * This is the model class for table "oauth_auth_code_t".
 *
 * @property integer     $id
 * @property string      $code_text
 * @property integer     $client_id
 * @property string      $redirect_uri_text
 * @property integer     $expire_time
 * @property string      $scope_text
 * @property string      $create_date
 * @property string      $lmod_date
 *
 * @property OAuthClient $client
 */
class OAuthAuthCode extends BaseAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return OAuthAuthCode the static model class
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
		return 'oauth_auth_code_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array( 'code_text, client_id, redirect_uri_text, expire_time', 'required' ),
			array(
				'client_id, expire_time',
				'numerical',
				'integerOnly' => true
			),
			array(
				'code_text',
				'length',
				'max' => 40
			),
			array(
				'redirect_uri_text',
				'length',
				'max' => 1024
			),
			array( 'scope_text', 'safe' ),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array(
				'id, code_text, client_id, redirect_uri_text, expire_time, scope_text',
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
			'code_text'         => 'Auth Code',
			'client_id'         => 'Client',
			'redirect_uri_text' => 'Redirect URI',
			'expire_time'       => 'Expiration Date',
			'scope_text'        => 'Scope',
		);
	}

}
