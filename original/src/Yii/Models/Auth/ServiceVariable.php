<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "service_variable_t"
 *
 * @property integer  $id
 * @property integer  $service_id
 * @property string   $name_text
 * @property string   $value_text
 * @property string   $create_date
 * @property string   $lmod_date
 *
 * Relations:
 * @property Service  $service
 */
class ServiceVariable extends BaseFabricAuthModel
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return ServiceVariableT the static model class
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
        return 'service_variable_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('service_id, name_text, create_date, lmod_date', 'required'),
            array('service_id', 'numerical', 'integerOnly' => true),
            array('name_text', 'length', 'max' => 128),
            array('value_text', 'safe'),
            array('id, service_id, name_text, value_text, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'service' => array(self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Auth\\Service', 'service_id'),
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
            'name_text'   => 'Name',
            'value_text'  => 'Value',
            'create_date' => 'Create Date',
            'lmod_date'   => 'Last Modified Date',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search( $returnCriteria = false )
    {
        $_criteria = new \CDbCriteria();

        $_criteria->compare( 'id', $this->id );
        $_criteria->compare( 'service_id', $this->service_id );
        $_criteria->compare( 'name_text', $this->name_text, true );
        $_criteria->compare( 'value_text', $this->value_text, true );
        $_criteria->compare( 'create_date', $this->create_date, true );
        $_criteria->compare( 'lmod_date', $this->lmod_date, true );

        if ( $returnCriteria )
        {
            return $_criteria;
        }

        return new \CActiveDataProvider(
            $this,
            array(
                'criteria' => $_criteria,
            )
        );
    }
}