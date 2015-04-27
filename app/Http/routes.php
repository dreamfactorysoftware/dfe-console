<?php
//******************************************************************************
//* Resource Controllers
//******************************************************************************

/**
 * resource controllers
 */
\Route::group(

    //['prefix' => 'v1', 'namespace' => 'DreamFactory\\Enterprise\\Console\\Http\\Controllers\\Resources', 'middleware' => 'auth'],
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
\Route::get(
    '/',
    function ()
    {
        /** @noinspection PhpUndefinedMethodInspection */

        return View::make(
            'app.dashboard',
            ['_trail' => null, '_active' => ['instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0], 'prefix' => 'v1']
        );

    }

    /*
    '/',
    [
        'as'         => 'home',
        'middleware' => 'auth',
        function ()
        {
            // @noinspection PhpUndefinedMethodInspection
            return \View::make(
                'app.dashboard',
                ['_trail' => null, '_active' => ['instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0]]
            );
        }
    ]
    */
);

\Route::get(
    '/home',

    function ()
    {
        /** @noinspection PhpUndefinedMethodInspection */

        return View::make(
            'app.dashboard',
            ['_trail' => null, '_active' => ['instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0], 'prefix' => 'v1']
        );

    }

);

//  Other controllers
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
