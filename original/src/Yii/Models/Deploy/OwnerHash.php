<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;
use DreamFactory\Interfaces\OwnerTypes;

/**
 * This is the model for table "owner_hash_t"
 *
 * @property integer $id
 * @property integer $owner_id
 * @property integer $owner_type_nbr
 * @property string  $hash_text
 * @property string  $create_date
 * @property string  $lmod_date
 */
class OwnerHash extends BaseFabricDeploymentModel implements OwnerTypes
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return OwnerHash the static model class
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
        return 'owner_hash_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('owner_id, owner_type_nbr, hash_text, create_date, lmod_date', 'required'),
            array('owner_id, owner_type_nbr', 'numerical', 'integerOnly' => true),
            array('hash_text', 'length', 'max' => 128),
            array('id, owner_id, owner_type_nbr, hash_text, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'             => 'ID',
            'owner_id'       => 'Owner',
            'owner_type_nbr' => 'Owner Type',
            'hash_text'      => 'Hash',
            'create_date'    => 'Create Date',
            'lmod_date'      => 'Last Modified Date',
        );
    }

}