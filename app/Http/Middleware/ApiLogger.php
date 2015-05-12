<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class ApiLogger
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Log all api requests
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        \Log::debug( '>>> api: ' . $request->getPathInfo() );

        /** @type Response $_response */
        $_response = $next( $request );

        \Log::debug( '<<< api: ' . $request->getPathInfo() . ' > ' . print_r( $_response->getContent(), true ) );

        return $_response;
    }

}
