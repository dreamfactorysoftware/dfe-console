<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;

/**
 * This is the model for table "environment_t"
 *
 * @property integer     $id
 * @property integer     $user_id
 * @property string      $environment_name_text
 * @property string      $create_date
 * @property string      $lmod_date
 *
 * Relations:
 * @property InstanceT[] $instanceTs
 */
class Environment extends BaseFabricDeploymentModel
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return EnvironmentT the static model class
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
        return 'environment_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('environment_name_text, create_date, lmod_date', 'required'),
            array('user_id', 'numerical', 'integerOnly' => true),
            array('environment_name_text', 'length', 'max' => 64),
            array('id, user_id, environment_name_text, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'instances' => array(self::HAS_MANY, 'Cerberus\\Yii\\Models\\Deploy\\Instance', 'environment_nbr'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'                    => 'ID',
            'user_id'               => 'User',
            'environment_name_text' => 'Environment Name',
            'create_date'           => 'Create Date',
            'lmod_date'             => 'Last Modified Date',
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
        $_criteria->compare( 'user_id', $this->user_id );
        $_criteria->compare( 'environment_name_text', $this->environment_name_text, true );
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