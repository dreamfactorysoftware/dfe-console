<?php
//******************************************************************************
//* Application Bootstrap
//******************************************************************************

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

if (!function_exists('__dfe_bootstrap')) {
    /**
     * @return \Illuminate\Foundation\Application
     */
    function __dfe_bootstrap()
    {
        //  Create the app
        $_app = new Illuminate\Foundation\Application(realpath(__DIR__ . '/../'));

        //  Bind our default services
        $_app->singleton('Illuminate\Contracts\Http\Kernel', DreamFactory\Enterprise\Console\Http\Kernel::class);
        $_app->singleton('Illuminate\Contracts\Console\Kernel', DreamFactory\Enterprise\Console\Console\Kernel::class);
        $_app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', DreamFactory\Enterprise\Console\Exceptions\Handler::class);

        //  Return the app
        return $_app;
    }
}

return __dfe_bootstrap();
