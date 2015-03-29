<?php
/**
 * index.php
 * Main entry point/bootstrap for all processes
 */
//	Load up composer...
use DreamFactory\Yii\Utility\Pii;

$_autoloader = require_once( __DIR__ . '/../vendor/autoload.php' );

//	Load framework if not defined yet...
if ( !class_exists( '\\Yii', false ) )
{
	require_once( __DIR__ . '/../vendor/dreamfactory/yii/framework/yii.php' );
}

//	Debug mode...
    ini_set( 'display_errors', true );
    ini_set( 'error_reporting', -1 );

   defined( 'YII_DEBUG' ) or define( 'YII_DEBUG', true );
    defined( 'YII_TRACE_LEVEL' ) or define( 'YII_TRACE_LEVEL', 3 );


//	Create the application and run
Pii::run(
	__DIR__,
	is_string( $_autoloader ) ? null : $_autoloader,
	null,
	null,
	true,
	true,
	false
);
