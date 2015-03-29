<?php
/**
 * VendorImage.php
 */
/**
 * This is the model class for table "vendor_image_t".
 *
 * @property integer     $id
 * @property integer     $vendor_id
 * @property string      $os_text
 * @property string      $license_text
 * @property string      $image_name_text
 * @property string      $image_id_text
 * @property string      $image_description_text
 * @property integer     $architecture_nbr
 * @property string      $region_text
 * @property string      $availability_zone_text
 * @property string      $root_storage_text
 * @property string      $create_date
 * @property string      $lmod_date
 *
 * The followings are the available model relations:
 * @property Instance[]  $instances
 * @property Vendor      $vendor
 */
class VendorImage extends BaseDeploymentModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return \DreamFactory\Yii\Models\Deployment\VendorImage
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
			array( 'vendor_id, image_name_text', 'required' ),
			array( 'vendor_id, architecture_nbr', 'numerical', 'integerOnly' => true ),
			array( 'os_text, license_text, image_name_text, region_text, availability_zone_text', 'length', 'max' => 64 ),
			array(
				'vendor_id, os_text, license_text, image_name_text, architecture_nbr, region_text, availability_zone_text',
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
			'instances' => array( static::HAS_MANY, 'Instance', 'vendor_image_id' ),
			'vendor'    => array( static::BELONGS_TO, 'Vendor', 'vendor_id' ),
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
			'os_text'                => 'Operating System',
			'license_text'           => 'License',
			'image_id_text'          => 'Image ID',
			'image_name_text'        => 'Image Name',
			'architecture_nbr'       => 'Architecture',
			'region_text'            => 'Region',
			'availability_zone_text' => 'Availability Zone',
			'create_date'            => 'Created',
			'lmod_date'              => 'Modified',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return \CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new \CDbCriteria;

		$_criteria->compare( 'vendor_id', $this->vendor_id );
		$_criteria->compare( 'os_text', $this->os_text, true );
		$_criteria->compare( 'license_text', $this->license_text, true );
		$_criteria->compare( 'image_name_text', $this->image_name_text, true );
		$_criteria->compare( 'architecture_nbr', $this->architecture_nbr );
		$_criteria->compare( 'region_text', $this->region_text, true );
		$_criteria->compare( 'availability_zone_text', $this->availability_zone_text, true );
		$_criteria->compare( 'create_date', $this->create_date, true );

		return new \CActiveDataProvider(
			$this,
			array(
				'criteria' => $_criteria,
			)
		);
	}
}