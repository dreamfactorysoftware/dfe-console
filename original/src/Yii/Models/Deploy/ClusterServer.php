<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;

/**
 * This is the model for table "cluster_server_asgn_t"
 *
 * @property int                  $user_id
 * @property int                  $cluster_id
 * @property int                  $server_id
 */
class ClusterServer extends BaseFabricDeploymentModel
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
        return 'cluster_server_asgn_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('cluster_id, server_id, create_date, lmod_date', 'required'),
            array('cluster_id, server_id, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'clusters' => array(
                static::HAS_MANY,
                'Cerberus\\Yii\\Models\\Deploy\\Cluster',
                'cluster_id'
            ),
            'servers'  => array(
                static::HAS_MANY,
                'Cerberus\\Yii\\Models\\Deploy\\Service',
                'server_id'
            ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'              => 'ID',
            'cluster_id_text' => 'Cluster ID',
            'subdomain_text'  => 'Subdomain',
            'create_date'     => 'Create Date',
            'lmod_date'       => 'Last Modified Date',
        );
    }
}