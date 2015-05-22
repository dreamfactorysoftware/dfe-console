<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Models\AppKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticateClient
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

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
    public function handle( Request $request, Closure $next )
    {
        $_token = $request->input( 'access-token' );
        $_clientId = $request->input( 'client-id' );

        /** @type AppKey $_key */
        $_key = AppKey::where( 'client_id', $_clientId )->first();

        if ( empty( $_key ) )
        {
            \Log::error( '     * auth.client: invalid "client-id" [' . $_clientId . ']' );

            return ErrorPacket::create( Response::HTTP_FORBIDDEN, 'Invalid "client-id"' );
        }

        if ( $_token != hash_hmac( config( 'dfe.signature-method', ConsoleDefaults::SIGNATURE_METHOD ), $_clientId, $_key->client_secret ) )
        {
            \Log::error( '     * auth.client fail: invalid "access-token" [' . $_token . ']' );

            return ErrorPacket::create( Response::HTTP_UNAUTHORIZED, 'Invalid "access-token"' );
        }

        try
        {
            $_owner = $this->_locateOwner( $_key->owner_id, $_key->owner_type_nbr );
        }
        catch ( ModelNotFoundException $_ex )
        {
            \Log::error( '     * auth.client: invalid "user" assigned to key id ' . $_key->id );

            return ErrorPacket::create( Response::HTTP_UNAUTHORIZED, 'Invalid credentials' );
        }

        \Log::info( '     * auth.client: access granted to "' . $_clientId . '"' );
        \Session::set( 'client.' . $_token, $_owner );

        return $next( $request );
    }

}