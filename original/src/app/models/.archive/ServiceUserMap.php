<?php
/**
 * This is the model class for table "service_user_map_t".
 *
 * @property integer          $id
 * @property integer          $user_id
 * @property integer          $service_id
 * @property integer          $service_config_id
 * @property string           $create_date
 * @property string           $lmod_date
 *
 * The followings are the available model relations:
 *
 * @property ServiceUser      $user
 * @property Service          $service
 * @property ServiceVariables $variables
 */
class ServiceUserMap extends \DreamFactory\Yii\Models\BaseModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ServiceUserMap the static model class
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
		return 'service_user_map_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'user_id, service_id, service_config_id', 'required' ),
			array( 'user_id, service_id, service_config_id', 'numerical', 'integerOnly' => true ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'user'    => array( self::BELONGS_TO, 'ServiceUser', 'user_id' ),
			'service' => array( self::BELONGS_TO, 'Service', 'service_id' ),
			//'variables' => array( self::HAS_MANY, 'ServiceVariables', 'map_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array_merge(
			parent::attributeLabels(),
			array(
				'user_id'           => 'User',
				'service_id'        => 'Service',
				'service_config_id' => 'Service Configuration',
			)
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = parent::search( true );

		$_criteria->compare( 'user_id', $this->user_id );
		$_criteria->compare( 'service_id', $this->service_id );

		return new CActiveDataProvider(
			$this,
			array(
				'criteria' => $_criteria,
			)
		);
	}
}