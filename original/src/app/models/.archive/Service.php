<?php
/**
 * Service.php
 */
/**
 * Service
 * This is the model class for table "service_t".
 *
 * @property int     $id
 * @property int     $service_type_nbr
 * @property string  $service_name_text
 * @property string  $service_tag_text
 * @property string  $description_text
 * @property string  $icon_url_text
 * @property string  $controller_class_text
 * @property string  $default_variables_text
 * @property int     $owner_id
 * @property int     $public_ind
 * @property int     $enable_ind
 * @property string  $create_date
 * @property string  $lmod_date
 */
class Service extends \DreamFactory\Yii\Models\BaseModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param null|string $className
	 *
	 * @return \BaseModel|\CActiveRecord|\Service the static model class
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
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'config'    => array( self::HAS_ONE, 'ServiceConfig', 'service_id' ),
			'variables' => array( self::HAS_MANY, 'ServiceVariable', 'service_id' ),
		);
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'service_type_nbr'       => 'Service Type',
			'service_name_text'      => 'Name',
			'service_tag_text'       => 'Service Tag',
			'controller_class_text'  => 'Handler Class',
			'default_variables_text' => 'Default Variables',
			'description_text'       => 'Description',
			'enable_ind'             => 'Enabled',
			'public_ind'             => 'Public',
		);
	}
}
