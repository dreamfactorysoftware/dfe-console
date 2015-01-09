<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'app', 'AppController' );
/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'fabric', 'FabricController' );
/** @noinspection PhpUndefinedMethodInspection */
Route::get(
    '/',
    function ()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return View::make( 'users' );
    }
);