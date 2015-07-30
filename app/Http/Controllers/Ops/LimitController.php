<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

class LimitController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'limit_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Limit';
    /** @type string */
    protected $_resource = 'policy';

    protected $_prefix = 'v1';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;


    public function getInstances($cluster_id)
    {

        return Instance::where('cluster_id', '=', $cluster_id)
            ->orderBy('instance_t.instance_name_text')
            ->get(['id', 'instance_name_text']);

    }

    public function getServices($instance_id)
    {
        $_instance = Instance::ByNameOrId($instance_id);

        $_url = $_instance->buildInstanceUrl();


    }

}
