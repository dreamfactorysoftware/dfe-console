<?php
define( 'LARAVEL_START', microtime( true ) );

//  Register The Composer Auto Loader
$_basePath = dirname( __DIR__ );
require $_basePath . '/vendor/autoload.php';

//  Include The Compiled Class File
$_compiledPath = $_basePath . '/storage/framework/compiled.php';

if ( file_exists( $_compiledPath ) )
{
    /** @noinspection PhpIncludeInspection */
    require $_compiledPath;
}
//  Check for laravel 5.1
elseif ( is_dir( __DIR__ . '/cache' ) )
{
    $_compiledPath = __DIR__ . '/cache/compiled.php';

    /** @noinspection PhpIncludeInspection */
    file_exists( $_compiledPath ) && require $_compiledPath;
}