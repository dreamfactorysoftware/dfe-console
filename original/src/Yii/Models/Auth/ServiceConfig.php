<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "service_config_t"
 *
 * @property integer  $id
 * @property integer  $service_id
 * @property integer  $active_ind
 * @property string   $active_date
 * @property string   $config_text
 * @property string   $expire_date
 * @property string   $create_date
 * @property string   $lmod_date
 *
 * Relations:
 * @property ServiceT $service
 */
class ServiceConfig extends BaseFabricAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ServiceConfigT the static model class
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
		return 'service_config_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'service_id, create_date, lmod_date', 'required' ),
			array( 'service_id, active_ind', 'numerical', 'integerOnly' => true ),
			array( 'active_date, config_text, expire_date', 'safe' ),
			array( 'id, service_id, active_ind, active_date, config_text, expire_date, create_date, lmod_date', 'safe', 'on' => 'search' ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'service' => array( self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Auth\\Service', 'service_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'service_id'  => 'Service',
			'active_ind'  => 'Active',
			'active_date' => 'Active Date',
			'config_text' => 'Config',
			'expire_date' => 'Expire Date',
			'create_date' => 'Create Date',
			'lmod_date'   => 'Last Modified Date',
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
		$_criteria->compare( 'service_id', $this->service_id );
		$_criteria->compare( 'active_ind', $this->active_ind );
		$_criteria->compare( 'active_date', $this->active_date, true );
		$_criteria->compare( 'config_text', $this->config_text, true );
		$_criteria->compare( 'expire_date', $this->expire_date, true );
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