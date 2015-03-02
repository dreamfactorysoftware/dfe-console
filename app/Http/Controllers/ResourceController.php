<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        try
        {
            return $this->_processDataRequest( $this->_tableName, call_user_func( array($this->_model, 'count') ) );
        }
        catch ( \Exception $_ex )
        {
            throw new BadRequestHttpException( $_ex->getMessage() );
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

    /** {@InheritDoc} */
    public function create()
    {
        return View::make( $this->_getResourceView(), array('model' => false) );
    }

    /** {@InheritDoc} */
    public function show( $id )
    {
        try
        {
            return Response::json(
                call_user_func( array($this->_model, 'findOrFail'), $id ),
                \Symfony\Component\HttpFoundation\Response::HTTP_OK
            );
        }
        catch ( \Exception $_ex )
        {
            throw new NotFoundHttpException();
        }
    }

    /** {@InheritDoc} */
    public function edit( $id )
    {
        try
        {
            $_model = call_user_func( array($this->_model, 'findOrFail'), $id );

            return View::make( $this->_getResourceView(), array('model' => $_model) );
        }
        catch ( \Exception $_ex )
        {
            throw new NotFoundHttpException();
        }
    }

    /** {@InheritDoc} */
    public function update( $id )
    {
    }

    /** {@InheritDoc} */
    public function destroy( $id )
    {
    }

    /**
     * @return string
     */
    protected function _getResourceView()
    {
        return $this->_resourceView ?: 'app.forms.' . $this->_resource;
    }
}
