<?php
/**
 * This is the model class for table "config_variable_t".
 *
 * @property integer    $id
 * @property integer    $user_config_id
 * @property string     $name_text
 * @property string     $value_text
 * @property string     $create_date
 * @property string     $lmod_date
 *
 * The followings are the available model relations:
 * @property UserConfig $config
 */
class ConfigVariable extends \DreamFactory\Yii\Models\BaseModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ServiceVariable the static model class
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
		return 'config_variable_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'user_config_id, name_text', 'required' ),
			array( 'user_config_id', 'numerical', 'integerOnly' => true ),
			array( 'name_text', 'length', 'max' => 128 ),
			array( 'value_text', 'safe' ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'config' => array( static::BELONGS_TO, 'UserConfig', 'user_config_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'             => 'ID',
			'user_config_id' => 'Configuration ID',
			'name_text'      => 'Name',
			'value_text'     => 'Value',
			'create_date'    => 'Create Date',
			'lmod_date'      => 'Modified Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria;

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'user_config_id', $this->user_config_id );
		$_criteria->compare( 'name_text', $this->name_text, true );
		$_criteria->compare( 'value_text', $this->value_text, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider(
			$this,
			array(
				'criteria' => $_criteria,
			)
		);
	}
}