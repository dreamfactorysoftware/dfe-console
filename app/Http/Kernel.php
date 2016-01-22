<?php namespace DreamFactory\Enterprise\Console\Http;

use DreamFactory\Enterprise\Common\Http\Middleware\ApiLogger;
use DreamFactory\Enterprise\Common\Http\Middleware\Authenticate;
use DreamFactory\Enterprise\Common\Http\Middleware\RedirectIfAuthenticated;
use DreamFactory\Enterprise\Common\Http\Middleware\VerifyCsrfToken;
use DreamFactory\Enterprise\Console\Http\Middleware\AuthenticateOpsClient;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
    ];
    /** @inheritdoc */
    protected $routeMiddleware = [
        'auth'                       => Authenticate::class,
        AuthenticateOpsClient::ALIAS => AuthenticateOpsClient::class,
        'csrf'                       => VerifyCsrfToken::class,
        'guest'                      => RedirectIfAuthenticated::class,
        ApiLogger::ALIAS             => ApiLogger::class,
    ];
}
