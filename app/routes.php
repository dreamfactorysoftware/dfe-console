<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'app', 'AppController' );
Route::get(
    '/',
    function ()
    {
        return View::make( 'users' );
    }
);