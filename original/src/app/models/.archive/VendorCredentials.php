<?php
/**
 * VendorCredentials.php
 */
/**
 * This is the model class for table "vendor_credentials_t".
 *
 * @property integer     $id
 * @property integer     $user_id
 * @property integer     $vendor_id
 * @property integer     $environment_id
 * @property string      $access_key_text
 * @property string      $secret_key_text
 * @property string      $extra_key_text
 * @property string      $create_date
 * @property string      $lmod_date
 *
 * The followings are the available model relations:
 * @property Instance[]  $instances
 * @property Vendor      $vendor
 */
class VendorCredentials extends BaseDeploymentModel
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return \VendorCredentials
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
			array( 'vendor_id, user_id, secret_key_text, access_key_text', 'required' ),
			array( 'secret_key_text, access_key_text, extra_key_text', 'length', 'max' => 256 ),
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
			'access_key_text'                   => 'Access Key/Token',
			'secret_key_text'                   => 'Secret Key/Token',
			'extra_key_text'                    => 'Extra Key/Token',
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
			'vendor'      => array( self::BELONGS_TO, 'Vendor', 'vendor_id' ),
			'environment' => array( self::BELONGS_TO, 'Environment', 'environment_id' ),
		);
	}

}