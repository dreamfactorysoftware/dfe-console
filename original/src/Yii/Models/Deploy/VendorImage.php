<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;

/**
 * This is the model for table "vendor_image_t"
 *
 * @property integer     $id
 * @property integer     $vendor_id
 * @property string      $os_text
 * @property string      $license_text
 * @property string      $image_id_text
 * @property string      $image_name_text
 * @property string      $image_description_text
 * @property integer     $architecture_nbr
 * @property string      $region_text
 * @property string      $availability_zone_text
 * @property string      $root_storage_text
 * @property string      $create_date
 * @property string      $lmod_date
 *
 * Relations:
 * @property Instance[]  $instances
 * @property Vendor      $vendor
 */
class VendorImage extends BaseFabricDeploymentModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return VendorImageT the static model class
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
		return 'vendor_image_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'vendor_id, image_id_text, create_date, lmod_date', 'required' ),
			array( 'vendor_id, architecture_nbr', 'numerical', 'integerOnly' => true ),
			array( 'os_text, license_text, image_id_text, region_text, availability_zone_text', 'length', 'max' => 64 ),
			array( 'image_name_text', 'length', 'max' => 256 ),
			array( 'root_storage_text', 'length', 'max' => 32 ),
			array( 'image_description_text', 'safe' ),
			array(
				'id, vendor_id, os_text, license_text, image_id_text, image_name_text, image_description_text, architecture_nbr, region_text, availability_zone_text, root_storage_text, create_date, lmod_date',
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
			'instances' => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Deploy\\Instance', 'vendor_image_id' ),
			'vendor'    => array( self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Deploy\\Vendor', 'vendor_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'vendor_id'              => 'Vendor',
			'os_text'                => 'Os',
			'license_text'           => 'License',
			'image_id_text'          => 'Image Id',
			'image_name_text'        => 'Image Name',
			'image_description_text' => 'Image Description',
			'architecture_nbr'       => 'Architecture',
			'region_text'            => 'Region',
			'availability_zone_text' => 'Availability Zone',
			'root_storage_text'      => 'Root Storage',
			'create_date'            => 'Create Date',
			'lmod_date'              => 'Last Modified Date',
		);
	}

}