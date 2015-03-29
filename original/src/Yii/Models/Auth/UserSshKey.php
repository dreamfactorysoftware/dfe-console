<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "user_ssh_key_t"
 *
 * @property integer $id
 * @property integer $user_id
 * @property string  $ssh_key_text
 * @property string  $fingerprint_text
 * @property string  $key_name_text
 * @property string  $create_date
 * @property string  $lmod_date
 */
class UserSshKey extends BaseFabricAuthModel
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return UserSshKeyT the static model class
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
        return 'user_ssh_key_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('user_id, ssh_key_text, fingerprint_text, key_name_text, create_date, lmod_date', 'required'),
            array('user_id', 'numerical', 'integerOnly' => true),
            array('fingerprint_text, key_name_text', 'length', 'max' => 64),
            array('id, user_id, ssh_key_text, fingerprint_text, key_name_text, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'               => 'ID',
            'user_id'          => 'User',
            'ssh_key_text'     => 'Ssh Key',
            'fingerprint_text' => 'Fingerprint',
            'key_name_text'    => 'Key Name',
            'create_date'      => 'Create Date',
            'lmod_date'        => 'Last Modified Date',
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
        $_criteria->compare( 'ssh_key_text', $this->ssh_key_text, true );
        $_criteria->compare( 'fingerprint_text', $this->fingerprint_text, true );
        $_criteria->compare( 'key_name_text', $this->key_name_text, true );
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