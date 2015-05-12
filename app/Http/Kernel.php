<?php
namespace DreamFactory\Enterprise\Console\Http;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;

class Kernel extends HttpKernel
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const CLASS_TO_REPLACE = 'Illuminate\Foundation\Bootstrap\ConfigureLogging';
    /**
     * @type string
     */
    const REPLACEMENT_CLASS = 'DreamFactory\Enterprise\Common\Bootstrap\CommonLoggingConfiguration';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'            => 'DreamFactory\Enterprise\Console\Http\Middleware\Authenticate',
        'auth.basic'      => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        'auth.client'     => 'DreamFactory\Enterprise\Console\Http\Middleware\AuthenticateClient',
        'csrf'            => 'DreamFactory\Enterprise\Console\Http\Middleware\VerifyCsrfToken',
        'guest'           => 'DreamFactory\Enterprise\Console\Http\Middleware\RedirectIfAuthenticated',
        'dfe.api-logging' => 'DreamFactory\Enterprise\Console\Http\Middleware\ApiLogger',
    ];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function __construct( Application $app, Router $router )
    {
        $this->_replaceClass();

        parent::__construct( $app, $router );
    }

    /**
     * @param string $fromClass The class to replace
     * @param string $toClass   The replacement
     */
    protected function _replaceClass( $fromClass = null, $toClass = null )
    {
        $_straps = array_flip( $this->bootstrappers );
        $fromClass = $fromClass ?: static::CLASS_TO_REPLACE;

        if ( array_key_exists( $fromClass, $_straps ) )
        {
            $this->bootstrappers[$_straps[$fromClass]] = $toClass ?: static::REPLACEMENT_CLASS;
        }
    }
}
