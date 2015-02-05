<?php
namespace App\Http\Controllers;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstanceController extends DataController
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
        $_columns =
            array(
                'instance_t.id',
                'instance_t.instance_id_text',
                'cluster_t.cluster_id_text',
                'instance_t.create_date',
                'user_t.email_addr_text',
                'user_t.lmod_date',
            );

        /** @type Builder $_query */
        $_query = Instance::join( 'user_t', 'instance_t.user_id', '=', 'user_t.id' )
            ->join( 'cluster_t', 'instance_t.cluster_id', '=', 'cluster_t.id' )
            ->select( $_columns );

        return $this->_processDataRequest( 'instance_t.instance_id_text', Instance::count(), $_columns, $_query );
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
            return Response::json( Instance::findOrFail( $id ), \Symfony\Component\HttpFoundation\Response::HTTP_OK );
        }
        catch ( \Exception $_ex )
        {
            throw new NotFoundHttpException();
        }
    }
}
