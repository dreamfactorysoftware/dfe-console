<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "auth_client_t"
 *
 * @property integer $id
 * @property string  $client_id_text
 * @property integer $owner_id
 * @property string  $app_name_text
 * @property string  $app_url_text
 * @property string  $client_secret_text
 * @property string  $redirect_uri_text
 * @property string  $create_date
 * @property string  $lmod_date
 */
class AuthClient extends BaseFabricAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return AuthClientT the static model class
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
		return 'auth_client_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'client_id_text, owner_id, app_name_text, client_secret_text, redirect_uri_text', 'required' ),
			array( 'owner_id', 'numerical', 'integerOnly' => true ),
			array( 'client_id_text, client_secret_text', 'length', 'max' => 200 ),
			array( 'app_name_text', 'length', 'max' => 60 ),
			array( 'app_url_text', 'safe' ),
			array(
				'id, client_id_text, owner_id, app_name_text, app_url_text, client_secret_text, redirect_uri_text, create_date, lmod_date',
				'safe',
				'on' => 'search'
			),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                 => 'ID',
			'client_id_text'     => 'Client Id',
			'owner_id'           => 'Owner',
			'app_name_text'      => 'App Name',
			'app_url_text'       => 'App Url',
			'client_secret_text' => 'Client Secret',
			'redirect_uri_text'  => 'Redirect Uri',
			'create_date'        => 'Create Date',
			'lmod_date'          => 'Last Modified Date',
		);
	}

}