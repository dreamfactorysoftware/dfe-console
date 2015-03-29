<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;
use Cerberus\Yii\Models\BaseFabricDeploymentModel;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Hasher;
use Kisma\Core\Utility\Log;

/**
 * This is the model class for table "fabric_auth.user_credentials_t".
 *
 * @property integer     $id
 * @property integer     $user_id
 * @property integer     $vendor_id
 * @property integer     $environment_id
 * @property string      $keys_text
 * @property string      $label_text
 * @property string      $create_date
 * @property string      $lmod_date
 */
class UserCredentials extends BaseFabricAuthModel
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @return array|null
	 */
	public function restMap()
	{
		return array(
			'id'             => 'id',
			'user_id'        => 'userId',
			'vendor_id'      => 'vendorId',
			'environment_id' => 'environment_id',
			'keys_text'      => 'keys',
			'label_text'     => 'label',
			'create_date'    => 'createDate',
			'lmod_date'      => 'lastModifiedDate'
		);
	}

	/**
	 * Convert object to serialized
	 *
	 * @return bool
	 */
	protected function beforeSave()
	{
		$_keys = $this->keys_text;

		if ( !is_string( $_keys ) )
		{
			$_keys = json_encode( $this->keys_text );
		}

		$this->keys_text = Hasher::encryptString( $_keys, Pii::getParam( 'auth.salt' ) );

		return parent::beforeSave();
	}

	/**
	 * Convert keys back to object
	 */
	protected function afterFind()
	{
		$_keys = Hasher::decryptString( $this->keys_text, Pii::getParam( 'auth.salt' ) );
		$this->keys_text = json_decode( $_keys, true );

		parent::afterFind();
	}

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return UserCredentials
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
		return 'user_credentials_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'vendor_id, user_id', 'required' ),
			array( 'label_text', 'length', 'max' => 60 ),
			array( 'keys_text', 'length', 'max' => 8192 ),
			array( 'user_id, vendor_id, environment_id', 'numerical', 'integerOnly' => true ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                                => 'ID',
			'user_id'                           => 'User ID',
			'vendor_id'                         => 'Vendor ID',
			'environment_id'                    => 'Environment',
			'create_date'                       => 'Create Date',
			'lmod_date'                         => 'Lmod Date',
			'keys_text'                         => 'Hash of keys',
			'environment.environment_name_text' => 'Environment',
			'label_text'                        => 'Label',
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'instances'   => array( self::HAS_MANY, 'Instance', 'vendor_credentials_id' ),
			'vendor'      => array( self::BELONGS_TO, 'Vendor', 'vendor_id' ),
			'environment' => array( self::BELONGS_TO, 'Environment', 'environment_id' ),
		);
	}
}