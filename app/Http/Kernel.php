<?php
namespace DreamFactory\Enterprise\Console\Http;

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
        'Barryvdh\Cors\Middleware\HandleCors',
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
}
