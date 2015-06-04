<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\VerifiesSignatures;
use DreamFactory\Enterprise\Database\Models\AppKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticateClient
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, VerifiesSignatures;

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

        //  Just plain ol' bad...
        if ( empty( $_token ) || empty( $_clientId ) )
        {
            \Log::error( '[auth.client] bad request: no token or client-id present' );

            return ErrorPacket::create( Response::HTTP_BAD_REQUEST );
        }

        /** @type AppKey $_key */
        try
        {
            $_key = AppKey::where( 'client_id', $_clientId )->firstOrFail();
        }
        catch ( \Exception $_ex )
        {
            \Log::error( '[auth.client] forbidden: invalid "client-id" [' . $_clientId . ']' );

            return ErrorPacket::create( Response::HTTP_FORBIDDEN, 'Invalid "client-id"' );
        }

        if ( !$this->_verifySignature( $_token, $_clientId, $_key->client_secret ) )
        {
            \Log::error( '[auth.client] bad request: signature verification fail' );

            return ErrorPacket::create( Response::HTTP_BAD_REQUEST );
        }

        try
        {
            $_owner = $this->_locateOwner( $_key->owner_id, $_key->owner_type_nbr );
        }
        catch ( ModelNotFoundException $_ex )
        {
            \Log::error( '[auth.client] unauthorized: invalid "user" assigned to akt#' . $_key->id );

            return ErrorPacket::create( Response::HTTP_UNAUTHORIZED );
        }

        $request->setUserResolver(
            function () use ( $_owner )
            {
                return $_owner;
            }
        );

        return $next( $request );
    }

}