<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Facades\Packet;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Console\Http\Controllers\DataController;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ResourceController extends DataController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The view name to render
     */
    protected $_resourceView;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return \Illuminate\Database\Query\Builder|mixed
     */
    protected function _loadData()
    {
        try {
            return $this->_processDataRequest($this->_tableName, call_user_func([$this->_model, 'count']));
        } catch (\Exception $_ex) {
            throw new BadRequestHttpException($_ex->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return $this->_loadData();
    }

    /** {@InheritDoc} */
    public function store()
    {
    }

    /**
     * @param array $viewData
     *
     * @return \Illuminate\View\View
     */
    public function create(array $viewData = [])
    {
        return \View::make($this->_getResourceView(),
            array_merge(['model' => false, 'pageHeader' => 'New ' . ucwords($this->_resource)], $viewData));
    }

    /** {@InheritDoc} */
    public function show($id)
    {
        try {
            return Packet::success(call_user_func([$this->_model, 'findOrFail'], $id));
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
    }

    /** {@InheritDoc} */
    public function edit($id)
    {
        try {
            $_model = call_user_func([$this->_model, 'findOrFail'], $id);

            return \View::make($this->_getResourceView(),
                ['model' => $_model, 'pageHeader' => 'Edit ' . ucwords($this->_resource)]);
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
    }

    /** {@InheritDoc} */
    public function update($id)
    {
        try {
            $_model = call_user_func([$this->_model, 'findOrFail'], $id);

            return \View::make($this->_getResourceView(),
                ['model' => $_model, 'pageHeader' => 'Edit ' . ucwords($this->_resource)]);
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
    }

    /** {@InheritDoc} */
    public function destroy($id)
    {
        try {
            $_model = call_user_func([$this->_model, 'findOrFail'], $id);

            if (!$_model->delete()) {
                return ErrorPacket::create(\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR,
                    'Delete of id "' . $id . '" failed.');
            }

            return SuccessPacket::make();
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
    }

    /**
     * @return string
     */
    protected function _getResourceView()
    {
        return $this->_resourceView ?: 'app.forms.' . $this->_resource;
    }
}
