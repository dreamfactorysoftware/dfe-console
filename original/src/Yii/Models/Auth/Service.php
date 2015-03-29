<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "service_t"
 *
 * @property integer            $id
 * @property integer            $service_class_nbr
 * @property integer            $service_type_nbr
 * @property string             $service_name_text
 * @property string             $service_tag_text
 * @property string             $icon_url_text
 * @property string             $description_text
 * @property string             $controller_class_text
 * @property string             $default_variables_text
 * @property integer            $owner_id
 * @property integer            $public_ind
 * @property integer            $enable_ind
 * @property string             $create_date
 * @property string             $lmod_date
 *
 * Relations:
 * @property ServiceConfigT[]   $serviceConfigTs
 * @property ServiceTokenT[]    $serviceTokenTs
 * @property ServiceUserMapT[]  $serviceUserMapTs
 * @property ServiceVariableT[] $serviceVariableTs
 */
class Service extends BaseFabricAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ServiceT the static model class
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
		return 'service_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'service_name_text, service_tag_text, owner_id, create_date, lmod_date', 'required' ),
			array( 'service_class_nbr, service_type_nbr, owner_id, public_ind, enable_ind', 'numerical', 'integerOnly' => true ),
			array( 'service_name_text', 'length', 'max' => 128 ),
			array( 'service_tag_text', 'length', 'max' => 64 ),
			array( 'icon_url_text', 'length', 'max' => 1024 ),
			array( 'controller_class_text', 'length', 'max' => 256 ),
			array( 'description_text, default_variables_text', 'safe' ),
			array(
				'id, service_class_nbr, service_type_nbr, service_name_text, service_tag_text, icon_url_text, description_text, controller_class_text, default_variables_text, owner_id, public_ind, enable_ind, create_date, lmod_date',
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
			'configs'   => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Auth\\ServiceConfig', 'service_id' ),
			'tokens'    => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Auth\\ServiceToken', 'service_id' ),
			'userMaps'  => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Auth\\ServiceUserMap', 'service_id' ),
			'variables' => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Auth\\ServiceVariable', 'service_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'service_class_nbr'      => 'Service Class',
			'service_type_nbr'       => 'Service Type',
			'service_name_text'      => 'Service Name',
			'service_tag_text'       => 'Service Tag',
			'icon_url_text'          => 'Icon Url',
			'description_text'       => 'Description',
			'controller_class_text'  => 'Controller Class',
			'default_variables_text' => 'Default Variables',
			'owner_id'               => 'Owner',
			'public_ind'             => 'Public',
			'enable_ind'             => 'Enable',
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
		$_criteria = new \CDbCriteria();

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'service_class_nbr', $this->service_class_nbr );
		$_criteria->compare( 'service_type_nbr', $this->service_type_nbr );
		$_criteria->compare( 'service_name_text', $this->service_name_text, true );
		$_criteria->compare( 'service_tag_text', $this->service_tag_text, true );
		$_criteria->compare( 'icon_url_text', $this->icon_url_text, true );
		$_criteria->compare( 'description_text', $this->description_text, true );
		$_criteria->compare( 'controller_class_text', $this->controller_class_text, true );
		$_criteria->compare( 'default_variables_text', $this->default_variables_text, true );
		$_criteria->compare( 'owner_id', $this->owner_id );
		$_criteria->compare( 'public_ind', $this->public_ind );
		$_criteria->compare( 'enable_ind', $this->enable_ind );
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