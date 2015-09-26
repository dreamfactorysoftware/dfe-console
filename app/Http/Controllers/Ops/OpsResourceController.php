<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\EnterpriseModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OpsResourceController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array|string The request input
     */
    protected $input;

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
        try {
            /** @type EnterpriseModel $_model */
            $_model = call_user_func([$this->model . '::create'], $this->scrubInput($request));

            return SuccessPacket::create($_model, Response::HTTP_CREATED);
        } catch (\Exception $_ex) {
            return ErrorPacket::create(null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Error creating resource: ' . $_ex->getMessage());
        }
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
        try {
            return SuccessPacket::create(call_user_func([$this->model, 'findOrFail'], $id));
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
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
            if ($_model->update($this->scrubInput($request))) {
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

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array|string
     */
    protected function scrubInput(Request $request)
    {
        $_input = $request->input();

        if (empty($_input)) {
            return [];
        }

        if (!is_array($_input)) {
            $_input = [$_input];
        }

        $_columns = \Schema::getColumnListing($this->tableName);

        foreach ($_input as $_key => $_value) {
            if (!in_array($_key, $_columns)) {
                unset($_input[$_key]);
            }
        }

        return $_input;
    }
}
