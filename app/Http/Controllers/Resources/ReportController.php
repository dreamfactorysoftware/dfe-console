<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;


class ReportController extends ResourceController
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

    protected $_prefix = 'v1';
    

    public function show($id)
    {
        return \View::make('app.reports.bandwidth')
            ->with('prefix', $this->_prefix)
            ->with('type', 'cluster-east-2');
    }

    public function index()
    {
        $clusters = \Cluster::all();
        $users = \User::all();
        $instances = \Instance::all();

        return \View::make('app.reports')
            ->with('prefix', $this->_prefix)
            ->with('clusters', $clusters)
            ->with('users', $users)
            ->with('instances', $instances)
            ->with('type', 'cluster-east-2');
    }

}


?>