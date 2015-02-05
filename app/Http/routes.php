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
Route::controller( 'app', 'AppController' );
/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'dashboard', 'DashboardController' );
/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'settings', 'SettingsController' );
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
        Route::resource( 'servers', 'ServerController' );
        Route::resource( 'clusters', 'ClusterController' );
        Route::resource( 'instances', 'InstanceController' );
        Route::resource( 'roles', 'RoleController' );
        Route::resource( 'service-users', 'ServiceUserController' );
        Route::resource( 'users', 'UserController' );
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
        'auth'     => 'Auth\AuthController',
        'password' => 'Auth\PasswordController',
    ]
);
