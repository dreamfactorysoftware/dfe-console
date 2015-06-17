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
        $_app->singleton('Illuminate\Contracts\Http\Kernel', 'DreamFactory\Enterprise\Console\Http\Kernel');
        $_app->singleton('Illuminate\Contracts\Console\Kernel', 'DreamFactory\Enterprise\Console\Console\Kernel');
        $_app->singleton('Illuminate\Contracts\Debug\ExceptionHandler',
            'DreamFactory\Enterprise\Console\Exceptions\Handler');

        //  Return the app
        return $_app;
    }
}

return __dfe_bootstrap();