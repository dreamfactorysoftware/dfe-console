<?php
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends BaseController
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
            $this->_parseDataRequest( 'email_addr_text' );

            $_response = DB::table( 'user_t' )
                ->orderBy( $this->_order )
                ->skip( $this->_skip )
                ->take( $this->_limit )
                ->get();

            return $this->_respond( $_response, User::count(), count( $_response ) );
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
            return Response::json( User::findOrFail( $id ), \Symfony\Component\HttpFoundation\Response::HTTP_OK );
        }
        catch ( \Exception $_ex )
        {
            throw new NotFoundHttpException();
        }
    }
}
