<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;

class HomeController extends ResourceController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function index()
    {
        return $this->renderView('app.home');
    }

}
