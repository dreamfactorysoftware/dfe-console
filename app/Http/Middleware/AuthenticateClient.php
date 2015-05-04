<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Library\Fabric\Database\Models\Deploy\AppKey;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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

        \Log::debug( 'auth.client: ' . print_r( $request->input(), true ) );

        /** @type AppKey $_key */
        $_key = AppKey::where( 'client_id', $_clientId )->first();

        if ( empty( $_key ) )
        {
            \Log::error( 'auth.client: invalid "client-id"' );

            return ErrorPacket::create( new BadRequestHttpException( 'Invalid "client-id"' ) );
        }

        if ( $_token != hash_hmac( config( 'dfe.signature-method', ConsoleDefaults::SIGNATURE_METHOD ), $_clientId, $_key->client_secret ) )
        {
            \Log::error( 'auth.client fail: invalid "access-token"' );

            return ErrorPacket::create( new UnauthorizedHttpException( 'Invalid "access-token"' ) );
        }

        if ( !$_key->user )
        {
            \Log::error( 'auth.client: invalid "user" assigned to key id ' . $_key->id );

            return ErrorPacket::create( new UnauthorizedHttpException( 'Invalid credentials' ) );
        }

        \Log::info( 'auth.client: access granted to "' . $_clientId . '"' );
        \Session::set( 'client.' . $_token, $_key->user );

        return $next( $request );
    }

}