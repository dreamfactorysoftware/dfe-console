<?php
use DreamFactory\Library\Fabric\Database\Models\Deploy\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleController extends BaseController
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
            $this->_parseDataRequest( 'role_name_text' );

            $_response = DB::table( 'role_t' )
                ->orderBy( $this->_order )
                ->skip( $this->_skip )
                ->take( $this->_limit )
                ->get();

            return $this->_respond( $_response, Role::count(), count( $_response ) );
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
            return Response::json( Role::findOrFail( $id ), \Symfony\Component\HttpFoundation\Response::HTTP_OK );
        }
        catch ( \Exception $_ex )
        {
            throw new NotFoundHttpException();
        }
    }
}
