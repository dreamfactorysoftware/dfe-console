<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "user_visit_t"
 *
 * @property string  $visit_date
 * @property integer $user_id
 * @property string  $visit_data_text
 */
class UserVisit extends BaseFabricAuthModel
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return UserVisitT the static model class
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
        return 'user_visit_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('visit_date', 'required'),
            array('user_id', 'numerical', 'integerOnly' => true),
            array('visit_data_text', 'safe'),
            array('visit_date, user_id, visit_data_text', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'visit_date'      => 'Visit Date',
            'user_id'         => 'User',
            'visit_data_text' => 'Visit Data',
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

        $_criteria->compare( 'visit_date', $this->visit_date, true );
        $_criteria->compare( 'user_id', $this->user_id );
        $_criteria->compare( 'visit_data_text', $this->visit_data_text, true );

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