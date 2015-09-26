<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Facades\Packet;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class ResourceController extends DataController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The view name to render
     */
    protected $resourceView;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return \Illuminate\Database\Query\Builder|mixed
     */
    protected function loadData()
    {
        try {
            return $this->processDataRequest($this->tableName, call_user_func([$this->model, 'count']));
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
        return $this->loadData();
    }

    /** {@InheritDoc} */
    public function store(Request $request)
    {
    }

    /**
     * @param array $viewData
     *
     * @return \Illuminate\View\View
     */
    public function create(array $viewData = [])
    {
        return $this->renderView($this->_getResourceView(),
            $viewData,
            [
                'model'      => false,
                'pageHeader' => 'New ' . ucwords($this->resource),
            ]);
    }

    /** {@InheritDoc} */
    public function show($id)
    {
        try {
            return Packet::success(call_user_func([$this->model, 'findOrFail'], $id));
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
    }

    /** {@InheritDoc} */
    public function edit($id)
    {
        try {
            $_model = call_user_func([$this->model, 'findOrFail'], $id);

            return $this->renderView($this->_getResourceView(),
                [
                    'model'      => $_model,
                    'pageHeader' => 'Edit ' . ucwords($this->resource),
                ]);
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return array|\Illuminate\View\View
     */
    public function update(Request $request, $id)
    {
        try {
            $_model = call_user_func([$this->model, 'findOrFail'], $id);

            return $this->renderView($this->_getResourceView(),
                ['model' => $_model, 'pageHeader' => 'Edit ' . ucwords($this->resource)]);
        } catch (\Exception $_ex) {
            return ErrorPacket::create();
        }
    }

    /** {@InheritDoc} */
    public function destroy($id)
    {
        try {
            $_model = call_user_func([$this->model, 'findOrFail'], $id);

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
        return $this->resourceView ?: 'app.forms.' . $this->resource;
    }

    /**
     * Builds a redirect url including any prefix
     *
     * @param string      $type
     * @param string|null $append Additional segments to add to url
     * @param bool|true   $usePrefix
     *
     * @return string
     */
    protected function makeRedirectUrl($type, $append = null, $usePrefix = true)
    {
        $_parts = $usePrefix ? [$this->getUiPrefix()] : [];
        $_parts[] = trim($type, ' /');
        is_string($append) && !empty($append) && $_parts[] = ltrim($append, ' /');

        return '/' . implode('/', $_parts);
    }
}
