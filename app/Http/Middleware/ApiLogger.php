<?php namespace DreamFactory\Enterprise\Console\Http\Middleware;

use Closure;

class ApiLogger
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
        \Log::debug( 'api: ' . $request->getPathInfo() . PHP_EOL . print_r( $request->input(), true ) );

        return $next( $request );
    }

}