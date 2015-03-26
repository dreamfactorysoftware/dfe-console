<?php
//******************************************************************************
//* Resource Controllers
//******************************************************************************

/**
 * resource controllers
 */
\Route::group(
    ['prefix' => 'api/v1', 'namespace' => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources', 'middleware' => 'auth'],
    function ()
    {
        \Route::resource( 'clusters', 'ClusterController' );
        \Route::resource( 'instances', 'InstanceController' );
        \Route::resource( 'roles', 'RoleController' );
        \Route::resource( 'servers', 'ServerController' );
        \Route::resource( 'service-users', 'ServiceUserController' );
        \Route::resource( 'users', 'UserController' );
    }
);

/**
 * Ops controller for operational api
 */
\Route::group(
    ['prefix' => 'api/v1', 'namespace' => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers'],
    function ()
    {
        \Route::controller( 'ops', 'OpsController' );
    }
);

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

//  Main page
\Route::get(
    '/',
    [
        'as'         => 'home',
        'middleware' => 'auth',
        function ()
        {
            /** @noinspection PhpUndefinedMethodInspection */
            return \View::make(
                'app.dashboard',
                ['_trail' => null, '_active' => ['instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0]]
            );
        }
    ]
);

//  Other controllers
\Route::controllers(
    [
        'app'       => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\AppController',
        'dashboard' => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\DashboardController',
        'settings'  => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\SettingsController',
        'auth'      => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Auth\\AuthController',
        'password'  => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Auth\\PasswordController',
        'instance'  => 'DreamFactory\\Enterprise\\Services\\Controllers\\InstanceController',
    ]
);

//******************************************************************************
//* Testing
//******************************************************************************

\Route::post(
    'form-submit',
    [
        'before' => 'csrf',
        function ()
        {
            //  validation;
        }
    ]
);
