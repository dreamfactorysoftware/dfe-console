<?php namespace DreamFactory\Enterprise\Console\Http;

use DreamFactory\Enterprise\Common\Http\Middleware\ApiLogger;
use DreamFactory\Enterprise\Console\Http\Middleware\Authenticate;
use DreamFactory\Enterprise\Console\Http\Middleware\AuthenticateOpsClient;
use DreamFactory\Enterprise\Console\Http\Middleware\RedirectIfAuthenticated;
use DreamFactory\Enterprise\Console\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
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
        'auth'                       => Authenticate::class,
        AuthenticateOpsClient::ALIAS => AuthenticateOpsClient::class,
        'csrf'                       => VerifyCsrfToken::class,
        'guest'                      => RedirectIfAuthenticated::class,
        ApiLogger::ALIAS             => ApiLogger::class,
    ];
}
