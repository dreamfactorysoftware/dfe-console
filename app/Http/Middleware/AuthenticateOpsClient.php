<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;
use DreamFactory\Enterprise\Common\Http\Middleware\BaseMiddleware;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\VerifiesSignatures;
use DreamFactory\Enterprise\Database\Models\AppKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Authenticates inbound API requests from the dashboard, etc.
 * Packets are signed/sent by "dreamfactory/dfe-ops-client" package.
 */
class AuthenticateOpsClient extends BaseMiddleware
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, VerifiesSignatures;

    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string My alias in the ioc and for logging
     */
    const ALIAS = 'auth.dfe-ops-client';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Authenticate an incoming request.f
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $_token = $request->input('access-token');
        $_clientId = $request->input('client-id');

        //  Just plain ol' bad...
        if (empty($_token) || empty($_clientId)) {
            $this->error('bad request: no token or client-id present');

            return ErrorPacket::create(Response::HTTP_BAD_REQUEST);
        }

        try {
            $_key = AppKey::byClientId($_clientId)->firstOrFail();
            $this->setSigningCredentials($_clientId, $_key->client_secret);
        } catch (\Exception $_ex) {
            $this->error('forbidden: invalid "client-id" [' . $_clientId . ']');

            return ErrorPacket::create(Response::HTTP_FORBIDDEN, 'Invalid "client-id"');
        }

        if (!$this->_verifySignature($_token, $_clientId, $_key->client_secret)) {
            $this->error('bad request: signature verification fail');

            return ErrorPacket::create(Response::HTTP_BAD_REQUEST);
        }

        try {
            $_owner = $this->_locateOwner($_key->owner_id, $_key->owner_type_nbr);
        } catch (ModelNotFoundException $_ex) {
            $this->error('unauthorized: invalid "user" assigned to akt#' . $_key->id);

            return ErrorPacket::create(Response::HTTP_UNAUTHORIZED);
        }

        $request->setUserResolver(function () use ($_owner){
            return $_owner;
        });

        //$this->debug('token validated for client "' . $_clientId . '"');

        return parent::handle($request, $next);
    }
}
