<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\Instance;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;

class InstanceController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $tableName = 'instance_t';
    /**
     * @type string
     */
    protected $model = Instance::class;
    /** @type string */
    protected $resource = 'instance';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    protected function _loadData()
    {
        $_columns = [
            'instance_t.id',
            'instance_t.instance_id_text',
            'cluster_t.cluster_id_text',
            'instance_t.create_date',
            'user_t.email_addr_text',
            'user_t.lmod_date',
        ];

        /** @type Builder $_query */
        $_query = Instance::join('user_t', 'instance_t.user_id', '=', 'user_t.id')->join('cluster_t',
            'instance_t.cluster_id',
            '=',
            'cluster_t.id')->select($_columns);

        return $this->processDataRequest('instance_t', Instance::count(), $_columns, $_query);
    }

}
