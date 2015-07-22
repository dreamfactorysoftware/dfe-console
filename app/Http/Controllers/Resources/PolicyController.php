<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;

class PolicyController extends ResourceController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'policy_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Limit';
    /** @type string */
    protected $_resource = 'policy';
    /**
     * @type string
     */
    protected $_prefix = 'v1';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param array $viewData
     *
     * @return \Illuminate\View\View
     */
    public function create(array $viewData = [])
    {
        return \View::make(
            'app.policies.create',
            [
                'prefix'   => $this->_prefix,
                'clusters' => Cluster::all(),
            ]
        );
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return
            \View::make('app.policies',
                [
                    'prefix'   => $this->_prefix,
                    'policies' => [],
                ]
            );
    }

    /**
     * @param string|int $clusterId
     *
     * @return \Illuminate\Database\Eloquent\Collection|Instance[]
     */
    public function getClusterInstances($clusterId)
    {
        $_cluster = $this->_findCluster($clusterId);

        return Instance::byClusterId($_cluster->id)
            ->orderBy('instance_name_text')
            ->get(['id', 'instance_name_text',]);
    }
}
