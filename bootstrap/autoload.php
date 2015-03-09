<?php
define( 'LARAVEL_START', microtime( true ) );

//  Register The Composer Auto Loader
require __DIR__ . '/../vendor/autoload.php';

//  Include The Compiled Class File
$_compiledPath = __DIR__ . '/../storage/framework/compiled.php';

if ( file_exists( $_compiledPath ) )
{
    require $_compiledPath;
}
