<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OpsResourceController extends ResourceController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            return SuccessPacket::create($_models = call_user_func([$this->model, 'all']));
        } catch (\Exception $_ex) {
            $this->error('Exception retrieving models: ' . $_ex->getMessage());

            return ErrorPacket::create($_ex);
        }
    }

    /**
     * Create a new resource
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $_resource = call_user_func([$this->model, 'create'], $request->input());

        if (empty($_resource)) {
            return ErrorPacket::create(null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Resource not found after creation.');
        }

        return SuccessPacket::create($_resource, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $_resource = call_user_func([$this->model, 'find'], $id);

        if (empty($_resource)) {
            return ErrorPacket::create();
        }

        return SuccessPacket::create($_resource, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $_model = call_user_func([$this->model, 'findOrFail'], $id);
            if ($_model->update($request->input())) {
                return SuccessPacket::create($_model, Response::HTTP_OK);
            }

            return ErrorPacket::create($_model, Response::HTTP_INTERNAL_SERVER_ERROR, 'Error updating resource.');
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $_resource = call_user_func([$this->model, 'find'], $id);

        if (empty($_resource)) {
            return ErrorPacket::create();
        }

        return SuccessPacket::create($_resource->delete(), Response::HTTP_OK);
    }
}
