<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
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

    public function index()
    {
        try {
            return SuccessPacket::create($_models = call_user_func([$this->model, 'all']));
        } catch (\Exception $_ex) {
            $this->error('Exception retrieving models: ' . $_ex->getMessage());

            return ErrorPacket::create($_ex);
        }
    }
}
