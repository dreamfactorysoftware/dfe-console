<?php
namespace DreamFactory\Enterprise\Common\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * A base class for DFE service providers
 */
abstract class BaseServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the alias to create
     */
    const ALIAS_NAME = false;
    /**
     * @type string The name of the service in the IoC
     */
    const IOC_NAME = false;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The class of this provider's service
     */
    protected $_serviceClass = null;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /**
     * Boot the service
     */
    public function boot()
    {
        $this->alias();
    }

    /**
     * Register a shared binding in the container.
     *
     * @param  string               $abstract
     * @param  \Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton( $abstract = null, $concrete )
    {
        //  Register object into instance container
        $this->app->singleton( $abstract ?: static::IOC_NAME, $concrete );
    }

    /**
     * Register a binding with the container.
     *
     * @param  string|array         $abstract
     * @param  \Closure|string|null $concrete
     * @param  bool                 $shared
     *
     * @return void
     */
    public function bind( $abstract = null, $concrete, $shared = false )
    {
        //  Register object into instance container
        $this->app->bind( $abstract ?: static::IOC_NAME, $concrete, $shared );
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [static::IOC_NAME];
    }

    /**
     * Call the $app's alias method
     *
     * @param string $class
     * @param string $alias
     */
    public function alias( $class = null, $alias = null )
    {
        $this->_serviceClass && $this->app->alias( $class ?: $this->_serviceClass, $alias ?: static::ALIAS_NAME );
    }

    /**
     * @return string
     */
    protected function _getClass()
    {
        static $_class = null;

        if ( !$_class )
        {
            $_class = get_class( $this ) ?: __CLASS__;
        }

        return $_class;
    }

    /**
     * @return string Returns this provider's IoC name
     */
    public function __invoke()
    {
        return static::IOC_NAME ?: null;
    }
}
