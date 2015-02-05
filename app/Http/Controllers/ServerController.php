<?php
namespace App\Http\Controllers;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServerController extends DataController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $_columns = array('server_t.id', 'server_t.server_id_text', 'server_type_t.type_name_text', 'server_t.host_text', 'server_t.lmod_date');

        /** @type Builder $_query */
        $_query = Server::join( 'server_type_t', 'server_t.server_type_id', '=', 'server_type_t.id' )->select( $_columns );

        return $this->_processDataRequest( 'instance_t.instance_id_text', Server::count(), $_columns, $_query );
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show( $id )
    {
        try
        {
            return Response::json( Server::findOrFail( $id ), \Symfony\Component\HttpFoundation\Response::HTTP_OK );
        }
        catch ( \Exception $_ex )
        {
            throw new NotFoundHttpException();
        }
    }
}
