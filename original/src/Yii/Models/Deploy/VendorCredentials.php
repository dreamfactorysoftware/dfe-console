<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;

/**
 * This is the model class for table "fabric_deploy.vendor_credentials_t".
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
class VendorCredentials extends BaseFabricDeploymentModel
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Convert object to serialized
	 *
	 * @return bool
	 */
	protected function beforeSave()
	{
		$this->keys_text = serialize( $this->keys_text );

		return parent::beforeSave();
	}

	/**
	 * Convert keys back to object
	 */
	protected function afterFind()
	{
		$this->keys_text = unserialize( $this->keys_text );

		parent::afterFind();
	}

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return \Cerberus\Yii\Models\BaseFabricAuthModel
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
		return 'vendor_credentials_t';
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
			'label_text'                        => 'Label',
			'environment.environment_name_text' => 'Environment',
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'instances'   => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Deploy\\Instance', 'vendor_credentials_id' ),
			'vendor'      => array( self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Deploy\\Vendor', 'vendor_id' ),
			'environment' => array( self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Deploy\\Environment', 'environment_id' ),
		);
	}
}