<?php
//******************************************************************************
//* Application Autoloader
//******************************************************************************

if ( !function_exists( '__dfe_autoload' ) )
{
    define( 'LARAVEL_START', microtime( true ) );

    /**
     * @return bool
     */
    function __dfe_autoload()
    {
        //  Register The Composer Auto Loader
        $_basePath = dirname( __DIR__ );
        require $_basePath . '/vendor/autoload.php';

        //  Laravel 5.0
        if ( file_exists( $_compiledPath = $_basePath . '/storage/framework/compiled.php' ) )
        {
            /** @noinspection PhpIncludeInspection */
            require $_compiledPath;
        }
        //  Check for laravel 5.1
        elseif ( is_dir( __DIR__ . '/cache' ) )
        {
            /** @noinspection PhpIncludeInspection */
            file_exists( $_compiledPath = __DIR__ . '/cache/compiled.php' ) && require $_compiledPath;
        }

        return true;
    }
}

return __dfe_autoload();

