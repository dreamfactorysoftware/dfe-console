<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;

class InstanceController extends ViewController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_tableName = 'instance_t';
    /**
     * @type string
     */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Instance';
    /** @type string */
    protected $_resource = 'instance';

    protected $_prefix = 'v1';

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
        //echo 'here';
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

        $test = $this->processDataRequest('instance_t', Instance::count(), $_columns, $_query);

        echo $test->count();

        return $this->processDataRequest('instance_t', Instance::count(), $_columns, $_query);
    }

    /*
    public function create( array $viewData = [] )
    {

        $clusters = new Cluster();
        $clusters_list = $clusters->all();

        return View::make( 'app.instances.create' )->with( 'prefix', $this->_prefix )->with( 'clusters', $clusters_list );


        return 'OK';
    }
    */

    public function edit($id)
    {
        return $this->renderView('app.instances.edit',
            [
                'instance_id' => $id,
                'instance'    => Instance::with(['user', 'cluster'])->find($id),
                'clusters'    => Cluster::all(),
            ]);
    }

    public function index()
    {
        return $this->renderView('app.instances', ['instances' => Instance::with(['user', 'cluster'])->get()]);
    }
}
