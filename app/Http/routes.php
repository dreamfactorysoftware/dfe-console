<?php
//******************************************************************************
//* Implicit Controllers
//******************************************************************************

//  Main page
\Route::get( '/', 'HomeController@index' );
\Route::get( 'home', 'HomeController@index' );

//******************************************************************************
//* Resource Controllers
//******************************************************************************

\Route::group(
    ['prefix' => 'v1', 'middleware' => 'auth'],
    function ()
    {
        //\Route::resource( 'dashboard', 'DashboardController' );
        \Route::resource( 'users', 'Resources\\UserController' );
        \Route::resource( 'servers', 'Resources\\ServerController' );
        \Route::resource( 'clusters', 'Resources\\ClusterController' );
        \Route::resource( 'instances', 'Resources\\InstanceController' );
        \Route::resource( 'policies', 'Resources\\PolicyController' );
        \Route::resource( 'reports', 'Resources\\ReportController' );
    }
);

//******************************************************************************
//* Other Controllers
//******************************************************************************

/** Ops controller for operational api */
\Route::group(
    ['prefix' => 'api/v1', 'middleware' => 'dfe.api-logging',],
    function ()
    {
        \Route::controller( 'ops', 'OpsController' );
    }
);

/** Miscellaneous controllers for dashboard functionality */
\Route::controllers(
    [
        //'app'       => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\AppController',
        'dashboard' => 'DashboardController',
        'settings'  => 'SettingsController',
        'auth'      => 'Auth\\AuthController',
        'password'  => 'Auth\\PasswordController',
        //'instance'  => 'DreamFactory\\Enterprise\\Services\\Controllers\\InstanceController',
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
