<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;

/**
 * This is the model for table "cluster_t"
 *
 * @property int                                               $user_id
 * @property string                                            $cluster_id_text
 * @property string                                            $subdomain_text
 * @property array                                             $servers
 */
class Cluster extends BaseFabricDeploymentModel
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return $this the static model class
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
        return 'cluster_t';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('id, cluster_id_text, subdomain_text, create_date, lmod_date', 'required'),
            array('id, user_id, cluster_id_text, subdomain_text, create_date, lmod_date', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'servers' => array(self::HAS_MANY, 'Cerberus\\Yii\\Models\\Deploy\\Server', 'server_id'),
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