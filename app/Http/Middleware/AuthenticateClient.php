<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;
use DreamFactory\Library\Fabric\Database\Models\Deploy\AppKey;
use DreamFactory\Library\Fabric\Database\Models\Deploy\User;
use Illuminate\Http\Response;

class AuthenticateClient
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Authenticate an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        $_token = $request->input( 'access-token' );
        $_clientId = $request->input( 'client-id' );
        $_userId = $request->input( 'user-id' );

        /** @type AppKey $_key */
        $_key = AppKey::where( 'client_id', $_clientId )->first();

        if ( empty( $_key ) )
        {
            \Log::error( 'auth.client fail: invalid "client-id"', ['token' => $_token, 'client-id' => $_clientId, 'user-id' => $_userId] );
            abort( Response::HTTP_BAD_REQUEST, 'The "client-id" is invalid.' );
        }

        if ( $_token != hash_hmac( 'sha256', $_clientId, $_key->client_secret ) )
        {
            \Log::error(
                'auth.client fail: invalid "access-token"',
                ['access-token' => $_token, 'client-id' => $_clientId, 'client-secret' => $_key->client_secret, 'user-id' => $_userId]
            );
            abort( Response::HTTP_UNAUTHORIZED );
        }

        $_user = User::find( $_userId );

        if ( empty( $_user ) )
        {
            \Log::error( 'auth.client fail: invalid "user-id"', ['token' => $_token, 'client-id' => $_clientId, 'user-id' => $_userId] );
            abort( Response::HTTP_BAD_REQUEST, 'The "user-id" is invalid.' );
        }

        \Log::info( 'auth.client pass', ['token' => $_token, 'client-id' => $_clientId, 'user-id' => $_userId] );
        \Session::set( 'client.' . $_token, $_user );

        return $next( $request );
    }

}
