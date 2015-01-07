<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/** @noinspection PhpUndefinedMethodInspection */
Route::get( '/', 'HomeController@showWelcome' );

/** @noinspection PhpUndefinedMethodInspection */
Route::get(
    'users',
    function ()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return View::make( 'users' );
    }
);
