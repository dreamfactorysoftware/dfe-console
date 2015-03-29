<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;

/**
 * This is the model for table "server_type_t"
 *
 * @property string               $type_name_text
 * @property string               $schema_text
 */
class ServerType extends BaseFabricDeploymentModel
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return Vendor the static model class
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
        return 'server_type_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('id, type_name_text, schema_text, create_date, lmod_date', 'required'),
            array('id, type_name_text, schema_text, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * {@InheritDoc}
     */
    protected function beforeSave()
    {
        if ( !empty( $this->schema_text ) && !is_string( $this->schema_text ) )
        {
            $this->schema_text = serialize( $this->schema_text );
        }

        return parent::beforeSave();
    }

    /**
     * {@InheritDoc}
     */
    protected function afterFind()
    {
        if ( !empty( $this->schema_text ) && is_string( $this->schema_text ) )
        {
            $this->schema_text = unserialize( $this->schema_text );
        }

        parent::afterFind();
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'servers' => array(self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Deploy\\Server', 'server_type_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'             => 'ID',
            'type_name_text' => 'Server ID',
            'schema_text'    => 'Schema',
            'create_date'    => 'Create Date',
            'lmod_date'      => 'Last Modified Date',
        );
    }
}