<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

abstract class ViewController extends ResourceController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

}
