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
        Route::resource( 'clusters', 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources\\ClusterController' );
        Route::resource( 'instances', 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources\\InstanceController' );
        Route::resource( 'roles', 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources\\RoleController' );
        Route::resource( 'servers', 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources\\ServerController' );
        Route::resource( 'service-users', 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources\\ServiceUserController' );
        Route::resource( 'service-users', 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources\\ServiceUserController' );
        Route::resource( 'users', 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources\\UserController' );
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
        'auth'      => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Auth\\AuthController',
        'password'  => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Auth\\PasswordController',
    ]
);

//******************************************************************************
//* Testing
//******************************************************************************

Route::get(
    'form',
    function ()
    {
        return View::make( 'app.forms.service-user' );
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
