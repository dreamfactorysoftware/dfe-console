<?php
//******************************************************************************
//* Resource Controllers
//******************************************************************************

/**
 * resource controllers
 */
\Route::group(
    ['prefix' => 'api/v1', 'middleware' => 'auth'],
    function ()
    {
        \Route::resource( 'clusters', 'Resources\\ClusterController' );
        \Route::resource( 'instances', 'Resources\\InstanceController' );
        \Route::resource( 'roles', 'Resources\\RoleController' );
        \Route::resource( 'servers', 'Resources\\ServerController' );
        \Route::resource( 'service-users', 'Resources\\ServiceUserController' );
        \Route::resource( 'users', 'Resources\\UserController' );
    }
);

/**
 * Ops controller for operational api
 */
\Route::group(
    ['prefix' => 'api/v1', 'middleware' => 'dfe.api-logging',],
    function ()
    {
        \Route::controller( 'ops', 'OpsController' );
    }
);

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

//  Main page
\Route::any( '/', ['uses' => 'HomeController@index'] );
\Route::any( 'home', ['uses' => 'HomeController@index'] );

//  Other controllers
\Route::controllers(
    [
        'app'       => 'AppController',
        'dashboard' => 'DashboardController',
        'settings'  => 'SettingsController',
        'auth'      => 'Auth\\AuthController',
        'password'  => 'Auth\\PasswordController',
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
