<?php
use DreamFactory\Library\Fabric\Database\Models\Deploy\Cluster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClusterController extends BaseController
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
            $this->_parseDataRequest( 'cluster_id_text' );

            $_response = DB::table( 'cluster_t' )
                ->orderBy( $this->_order )
                ->skip( $this->_skip )
                ->take( $this->_limit )
                ->get();

            return $this->_respond( $_response, Cluster::count(), count( $_response ) );
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
