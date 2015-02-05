<?php
namespace DreamFactory\Enterprise\Console\Controllers;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Cluster;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClusterController extends DataController
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
        try
        {
            $_columns =
                array(
                    'id',
                    'cluster_id_text',
                    'subdomain_text',
                    'lmod_date',
                );

            return $this->_processDataRequest( 'cluster_t', Cluster::count(), $_columns );
        }
        catch ( \Exception $_ex )
        {
            throw new BadRequestHttpException( $_ex->getMessage() );
        }
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
            return Response::json( Cluster::findOrFail( $id ), \Symfony\Component\HttpFoundation\Response::HTTP_OK );
        }
        catch ( \Exception $_ex )
        {
            throw new NotFoundHttpException();
        }
    }
}
