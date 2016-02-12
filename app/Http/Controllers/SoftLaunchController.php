<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

class SoftLaunchController extends FactoryController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function index()
    {
        return view('soft-launch.layout');
    }
}
