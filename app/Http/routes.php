<?php
//******************************************************************************
//* Console Routes
//******************************************************************************

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

//******************************************************************************
//* Resource Controllers
//******************************************************************************

Route::group(
    ['prefix' => 'api/v1'],
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

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

//  Main page
Route::get(
    '/',
    function ()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return View::make(
            'app.dashboard',
            ['_trail' => null, '_active' => ['instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0]]
        );
    }
);

//  Other controllers
Route::controllers(
    [
        'app'       => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\AppController',
        'dashboard' => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\DashboardController',
        'settings'  => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\SettingsController',
        'auth'      => 'DreamFactory\Enterprise\Console\\Http\\Controllers\\Auth\AuthController',
        'password'  => 'DreamFactory\Enterprise\Console\\Http\\Controllers\\Auth\PasswordController',
    ]
);

//******************************************************************************
//* Testing
//******************************************************************************

Route::get(
    'form',
    function ()
    {
        return View::make( 'app.forms.user' );
    }
);

Route::post(
    'form-submit',
    [
        'before' => 'csrf',
        function ()
        {
            //  validation;
        }
    ]
);
