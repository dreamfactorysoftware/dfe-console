<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;

class ReportController extends ViewController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'report_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Report';
    /** @type string */
    protected $_resource = 'report';
    /**
     * @type string
     */
    protected $_prefix = 'v1';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function show($id)
    {
        return $this->index();
    }

    /** @inheritdoc */
    public function index()
    {
        $_connection = config('reports.default');

        return \View::make('app.reports',
            [
                'prefix'            => $this->_prefix,
                'clusters'          => Cluster::orderBy('cluster_id_text')->get(['cluster_id_text']),
                'users'             => User::orderBy('first_name_text')->orderBy('last_name_text')->get([
                    'email_addr_text',
                    'first_name_text',
                    'last_name_text',
                ]),
                'instances'         => Instance::orderBy('instance_id_text')->get(['instance_id_text']),
                'report_index_type' => config('reports.connections.' . $_connection . '.reports.api-usage.index-type',
                    config('dfe.cluster-id')),
                'report_base_uri'   => config('reports.connections.' . $_connection . '.base-uri'),
                'report_title'      => config('reports.connections.' . $_connection . '.reports.api-usage.title'),
                'report_query'      => config('reports.connections.' . $_connection . '.reports.api-usage.query'),
            ]);
    }
}
