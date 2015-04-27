<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use Illuminate\Support\Facades\View;
//use Illuminate\Html\HtmlServiceProvider;
use Illuminate\Html\FormFacade;

class ReportController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'report_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Report';
    /** @type string */
    protected $_resource = 'report';

    protected $_prefix = 'v1';


    public function index()
    {




        return View::make('app.reports')->with('prefix', $this->_prefix);//.index');//->with('nerd', $test);
    }

}


?>