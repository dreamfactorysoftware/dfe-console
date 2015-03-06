<?php namespace DreamFactory\Enterprise\Common\Traits;

use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * A trait that adds a Guard property to a class
 *
 * @package DreamFactory\Enterprise\Common\Traits
 */
trait GuardFilter
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @var Guard The Guard implementation.
     */
    protected $_guard;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param Guard $guard
     */
    public function __construct( Guard $guard )
    {
        $this->_guard = $guard;
    }

    /**
     * Expose the Guard::check() method
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return bool
     */
    public function check( $request, Closure $next )
    {
        return $this->_guard && $this->_guard->check();
    }

    /**
     * @return bool
     */
    public function guest()
    {
        return $this->_guard && $this->_guard->guest();
    }

}
