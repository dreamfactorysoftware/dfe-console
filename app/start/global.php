<?php
use Illuminate\Support\ClassLoader;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

//******************************************************************************
//* Global Application Settings
//******************************************************************************

$_appPath = app_path();

/**
 * Register The Laravel Class Loader
 */
ClassLoader::addDirectories(
    array(
        $_appPath . '/commands',
        $_appPath . '/controllers',
        $_appPath . '/models',
        $_appPath . '/database/seeds',
    )
);

/**
 * Application Error Logger
 */
Log::useFiles( storage_path() . '/logs/laravel.log' );

/**
 * Application Error Handler
 */
App::error(
    function ( Exception $exception, $code )
    {
        Log::error( $exception );
    }
);

/**
 * Maintenance Mode Handler
 */
App::down(
    function ()
    {
        return Response::make( "Be right back!", 503 );
    }
);

/**
 * Requirements: filters & helpers
 */
/** @noinspection PhpIncludeInspection */
require $_appPath . '/filters.php';
/** @noinspection PhpIncludeInspection */
require $_appPath . '/helpers.php';
/** @noinspection PhpIncludeInspection */
require $_appPath . '/extensions.php';

//  Cleanup
unset( $_appPath );
