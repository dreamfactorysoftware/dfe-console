<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\Cluster;


class LimitController extends ResourceController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'limit_t';
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
            'app.limits.create',
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
            \View::make('app.limits',
                [
                    'prefix'   => $this->_prefix,
                    'limits' => [],
                ]
            );
    }
}

