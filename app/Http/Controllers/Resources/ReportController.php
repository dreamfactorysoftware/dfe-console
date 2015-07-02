<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

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

    public function index()
    {
        return \View::make('app.reports')
            ->with('prefix', $this->_prefix)
            ->with('type', 'cluster-east-2');
    }

    public function show($id)
    {

        return \View::make('app.reports.bandwidth')
            ->with('prefix', $this->_prefix)
            ->with('type', 'cluster-east-2');
    }

}

?>