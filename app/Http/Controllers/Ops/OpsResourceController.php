<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Console\Http\Controllers\Resources\ResourceController;
use DreamFactory\Enterprise\Console\Http\Middleware\AuthenticateOpsClient;

class OpsResourceController extends ResourceController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * ctor: ensure proper middleware selected
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware(AuthenticateOpsClient::ALIAS);
    }

}
