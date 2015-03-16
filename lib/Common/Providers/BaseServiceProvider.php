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
    /**
     * @type string The name of the manager service in the IoC
     */
    const MANAGER_IOC_NAME = false;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The class of this provider's service
     */
    protected $_serviceClass = null;
    /**
     * @type bool No need to be eager unless wanted...
     */
    protected $defer = true;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

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

    /**
     * Redirect unknown methods to $app
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call( $method, $parameters )
    {
        if ( method_exists( $this->app, $method ) )
        {
            return call_user_func_array( [$this->app, $method], $parameters );
        }

        return parent::__call( $method, $parameters );
    }

}
