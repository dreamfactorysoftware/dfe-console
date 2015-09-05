<?php
//******************************************************************************
//* Application Bootstrap
//******************************************************************************

if (!function_exists('__dfe_bootstrap')) {
    /**
     * @return \Illuminate\Foundation\Application
     */
    function __dfe_bootstrap()
    {
        //  Create the app
        $_app = new Illuminate\Foundation\Application(realpath(dirname(__DIR__)));

        //  Bind our default services
        $_app->singleton(Illuminate\Contracts\Http\Kernel::class, DreamFactory\Enterprise\Console\Http\Kernel::class);
        $_app->singleton(Illuminate\Contracts\Console\Kernel::class,
            DreamFactory\Enterprise\Console\Console\Kernel::class);
        $_app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class,
            DreamFactory\Enterprise\Console\Exceptions\Handler::class);

        //  Return the app
        return $_app;
    }
}

return __dfe_bootstrap();