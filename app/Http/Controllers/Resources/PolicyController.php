<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;

use DreamFactory\Enterprise\Database\Models\Cluster;

class PolicyController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'policy_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Policy';
    /** @type string */
    protected $_resource = 'policy';

    protected $_prefix = 'v1';

    public function create(array $viewData = [])
    {
        $clusters = new Cluster();
        $clusters_list = $clusters->all();

        return \View::make('app.policies.create', ['prefix' => $this->_prefix])
            ->with('clusters', $clusters_list);
    }

    public function index()
    {
        return \View::make('app.policies')->with('prefix', $this->_prefix)->with('policies', []);
    }
}
