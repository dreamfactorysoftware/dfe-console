<?php
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\ServiceUser;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceUserController extends BaseDataController
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
                'service_user_t',
                ServiceUser::count(),
                array('id', 'first_name_text', 'last_name_text', 'email_addr_text', 'lmod_date')
            );
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
