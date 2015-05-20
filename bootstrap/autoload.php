<?php
define( 'LARAVEL_START', microtime( true ) );

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