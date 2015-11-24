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
    public function getKibana()
    {
        $url = env('DFE_AUDIT_CLIENT_HOST');
        $url .= ':'.(env('DFE_AUDIT_CLIENT_PORT') ? env('DFE_AUDIT_CLIENT_PORT') : '5601');

        return \Redirect::to($url);
    }

    /** @inheritdoc */
    public function show($id)
    {
        return;
    }

    /** @inheritdoc */
    public function index()
    {
        return \View::make('app.reports',
            [
                'prefix'            => $this->_prefix
            ]);
    }
}
