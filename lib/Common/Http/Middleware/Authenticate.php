<?php namespace DreamFactory\Enterprise\Common\Http\Middleware;

use Closure;
use DreamFactory\Enterprise\Common\Traits\GuardFilter;
use Illuminate\Contracts\Auth\Guard;

class Authenticate
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use GuardFilter
    {
        GuardFilter::__construct as protected __guardFilter__construct( Guard $guard );
    };

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new filter instance.
     *
     * @param  Guard $auth
     */
    public function __construct( Guard $auth )
    {
        //  Call our trait constructor
        $this->__guardFilter__construct( $auth );
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        if ( !$this->guest() )
        {
            return $next( $request );
        }

        if ( $request->ajax() )
        {
            return response( 'Unauthorized.', 401 );
        }

        return redirect()->guest( 'auth/login' );
    }

}
