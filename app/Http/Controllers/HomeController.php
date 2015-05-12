<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

class HomeController extends FactoryController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function index()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return \View::make(
            'app.dashboard',
            ['_trail' => null, '_active' => ['instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0], 'prefix' => 'v1',]
        );
    }

}
