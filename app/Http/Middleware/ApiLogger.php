<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;

/**
 * Simple middleware that logs api requests to the log
 */
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
        \Log::debug( '[api.logging] ' . $request->getMethod() . ': ' . $request->getPathInfo() );

        return $next( $request );
    }

}
