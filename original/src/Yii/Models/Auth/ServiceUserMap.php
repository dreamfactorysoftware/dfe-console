<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "service_user_map_t"
 *
 * @property integer      $id
 * @property integer      $user_id
 * @property integer      $service_id
 * @property integer      $service_config_id
 * @property string       $create_date
 * @property string       $lmod_date
 *
 * Relations:
 * @property Service      $service
 * @property ServiceUser  $user
 */
class ServiceUserMap extends BaseFabricAuthModel
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return ServiceUserMapT the static model class
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
            array('user_id, service_id, service_config_id, create_date, lmod_date', 'required'),
            array('user_id, service_id, service_config_id', 'numerical', 'integerOnly' => true),
            array('id, user_id, service_id, service_config_id, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'service' => array(self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Auth\\Service', 'service_id'),
            'user'    => array(self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Auth\\ServiceUser', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'                => 'ID',
            'user_id'           => 'User',
            'service_id'        => 'Service',
            'service_config_id' => 'Service Config',
            'create_date'       => 'Create Date',
            'lmod_date'         => 'Last Modified Date',
        );
    }
}