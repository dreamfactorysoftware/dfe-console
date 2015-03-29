<?php
/**
 * Vendor.php
 */
/**
 * This is the model class for table "vendor_t".
 *
 * @property integer                                          $id
 * @property string                                           $vendor_name_text
 * @property string                                           $create_date
 * @property string                                           $lmod_date
 *
 * The followings are the available model relations:
 * @property \Cerberus\Yii\Models\Deploy\Instance[]           $instances
 * @property VendorImage[]                                    $images
 */
class Vendor extends BaseDeploymentModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return Vendor
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
		return 'vendor_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'vendor_name_text', 'required' ),
			array( 'vendor_name_text', 'length', 'max' => 48 ),
			array( 'id, vendor_name_text, create_date, lmod_date', 'safe', 'on' => 'search' ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'instances' => array( self::HAS_MANY, 'Cerberus.Yii.Models.Instance', 'vendor_id' ),
			'images'    => array( self::HAS_MANY, 'VendorImage', 'vendor_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'               => 'ID',
			'vendor_name_text' => 'Vendor Name',
			'create_date'      => 'Create Date',
			'lmod_date'        => 'Lmod Date',
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

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'vendor_name_text', $this->vendor_name_text, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new \CActiveDataProvider(
			$this,
			array(
				 'criteria' => $_criteria,
			)
		);
	}
}