<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;

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

        $_response = $next( $request );

        \Log::debug( '<<< api: ' . $request->getPathInfo() . ' > ' . print_r( $_response, true ) );

        return $_response;
    }

}
