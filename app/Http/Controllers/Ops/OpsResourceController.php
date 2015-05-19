<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Console\Http\Controllers\Resources\ResourceController;

class OpsResourceController extends ResourceController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * ctor: ensure auth.client selected
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware( 'auth.client' );
    }

}
