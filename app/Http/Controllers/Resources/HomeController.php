<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;


class HomeController extends ResourceController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function __construct()
    {
        parent::__construct();

        $this->_active = [];
    }

    public function index()
    {
        return \View::make('app.home')->with('prefix', 'v1');
    }

}
