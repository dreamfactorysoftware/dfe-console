<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function __construct( Request $request )
    {
        //  require auth'd users
        $this->middleware( 'auth' );
    }

    public function index()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return \View::make(
            'app.dashboard',
            ['_trail' => null, '_active' => ['instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0]]
        );
    }

}
