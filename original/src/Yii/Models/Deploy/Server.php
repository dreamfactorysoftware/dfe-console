<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;

/**
 * This is the model for table "server_t"
 *
 * @property int                  $server_type_id
 * @property string               $server_id_text
 * @property string               $host_text
 * @property string               $config_text
 *
 * Relations:
 * @property Cluster              $cluster
 * @property ServerType           $serverType
 */
class Server extends BaseFabricDeploymentModel
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
        return 'server_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('id, server_type_id, host_text, server_id_text, create_date, lmod_date', 'required'),
            array('id, server_type_id, host_text, server_id_text, config_text, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * {@InheritDoc}
     */
    protected function beforeSave()
    {
        if ( !empty( $this->config_text ) && !is_string( $this->config_text ) )
        {
            $this->config_text = json_encode( $this->config_text, JSON_UNESCAPED_SLASHES );
        }

        return parent::beforeSave();
    }

    /**
     * {@InheritDoc}
     */
    protected function afterFind()
    {
        if ( !empty( $this->config_text ) && is_string( $this->config_text ) )
        {
            $this->config_text = json_decode( $this->config_text, true );
        }

        parent::afterFind();
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'serverType' => array(self::HAS_ONE, 'Cerberus\\Yii\\Models\\Deploy\\ServerType', 'server_type_id'),
            'cluster'    => array(self::HAS_ONE, 'Cerberus\\Yii\\Models\\Deploy\\ClusterServer', 'server_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'             => 'ID',
            'server_id_text' => 'Server ID',
            'host_text'      => 'Host',
            'config_text'    => 'Settings',
            'create_date'    => 'Create Date',
            'lmod_date'      => 'Last Modified Date',
        );
    }
}