<?php
namespace App\Http\Controllers;

use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends DataController
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
        return
            $this->_processDataRequest(
                'user_t',
                User::count(),
                array('id', 'first_name_text', 'last_name_text', 'email_addr_text', 'lmod_date')
            );
    }

    public function update()
    {

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
            return Response::json( User::findOrFail( $id ), Response::HTTP_OK );
        }
        catch ( \Exception $_ex )
        {
            throw new NotFoundHttpException();
        }
    }
}
