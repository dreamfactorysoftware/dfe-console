<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "service_token_t"
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
 * Relations:
 * @property ServiceT     $service
 * @property ServiceUserT $user
 */
class ServiceToken extends BaseFabricAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ServiceTokenT the static model class
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
			array( 'service_id, user_id, create_date, lmod_date', 'required' ),
			array( 'service_id, user_id', 'numerical', 'integerOnly' => true ),
			array( 'token_text, token_secret_text, refresh_token_text', 'length', 'max' => 64 ),
			array( 'expire_date, issue_date', 'safe' ),
			array(
				'id, service_id, user_id, token_text, token_secret_text, refresh_token_text, expire_date, issue_date, create_date, lmod_date',
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
			'service' => array( self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Auth\\Service', 'service_id' ),
			'user'    => array( self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Auth\\ServiceUser', 'user_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                 => 'ID',
			'service_id'         => 'Service',
			'user_id'            => 'User',
			'token_text'         => 'Token',
			'token_secret_text'  => 'Token Secret',
			'refresh_token_text' => 'Refresh Token',
			'expire_date'        => 'Expire Date',
			'issue_date'         => 'Issue Date',
			'create_date'        => 'Create Date',
			'lmod_date'          => 'Last Modified Date',
		);
	}

}