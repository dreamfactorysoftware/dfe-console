<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'app', '\\DreamFactory\\Enterprise\\Console\\Controllers\\AppController' );
/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'dashboard', '\\DreamFactory\\Enterprise\\Console\\Controllers\\DashboardController' );
/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'settings', '\\DreamFactory\\Enterprise\\Console\\Controllers\\SettingsController' );
/** @noinspection PhpUndefinedMethodInspection */
Route::get(
    'form',
    function ()
    {
        return View::make( 'app.forms.user' );
    }
);
/** @noinspection PhpUndefinedMethodInspection */
Route::post(
    'form-submit',
    array(
        'before' => 'csrf',
        function ()
        {
            //  validation;
        }
    )
);
/** @noinspection PhpUndefinedMethodInspection */
Route::group(
    array('prefix' => 'api/v1'),
    function ()
    {
        Route::resource( 'servers', 'DreamFactory\\Enterprise\\Console\\Controllers\\ServerController' );
        Route::resource( 'clusters', 'DreamFactory\\Enterprise\\Console\\Controllers\\ClusterController' );
        Route::resource( 'instances', 'DreamFactory\\Enterprise\\Console\\Controllers\\InstanceController' );
        Route::resource( 'roles', 'DreamFactory\\Enterprise\\Console\\Controllers\\RoleController' );
        Route::resource( 'service-users', 'DreamFactory\\Enterprise\\Console\\Controllers\\ServiceUserController' );
        Route::resource( 'users', 'DreamFactory\\Enterprise\\Console\\Controllers\\UserController' );
    }
);
/** @noinspection PhpUndefinedMethodInspection */
Route::get(
    '/',
    function ()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return View::make(
            'app.dashboard',
            array('_trail' => null, '_active' => array('instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0))
        );
    }
);

Route::controllers(
    [
        'auth'     => 'App\\Http\\Controllers\\Auth\AuthController',
        'password' => 'App\\Http\\Controllers\\Auth\PasswordController',
    ]
);
