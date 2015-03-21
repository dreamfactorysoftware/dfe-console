<?php
//******************************************************************************
//* Application Bootstrap
//******************************************************************************

//  Create the app
$_path = realpath( dirname( __DIR__ ) );
$_app = new Illuminate\Foundation\Application( $_path );

//  Bind our default services
$_app->singleton( 'Illuminate\Contracts\Http\Kernel', 'DreamFactory\Enterprise\Console\Http\Kernel' );
$_app->singleton( 'Illuminate\Contracts\Console\Kernel', 'DreamFactory\Enterprise\Console\Console\Kernel' );
$_app->singleton( 'Illuminate\Contracts\Debug\ExceptionHandler', 'DreamFactory\Enterprise\Console\Exceptions\Handler' );

//  Return the app
return $_app;
