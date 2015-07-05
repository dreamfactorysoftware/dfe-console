<?php
//******************************************************************************
//* Implicit Controllers
//******************************************************************************

//  Main page
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

\Route::group(['middleware' => 'auth'],
    function () {
        \Route::get('/', 'Resources\\HomeController@index');
        \Route::get('home', 'Resources\\HomeController@index');
    });

//******************************************************************************
//* Resource Controllers
//******************************************************************************

\Route::group(['prefix' => ConsoleDefaults::UI_PREFIX, 'middleware' => 'auth'],
    function () {
        \Route::resource('home', 'Resources\\HomeController');
        \Route::resource('users', 'Resources\\UserController');
        \Route::resource('servers', 'Resources\\ServerController');
        \Route::resource('clusters', 'Resources\\ClusterController');
        \Route::resource('instances', 'Resources\\InstanceController');
        \Route::resource('policies', 'Resources\\PolicyController');
        \Route::resource('reports', 'Resources\\ReportController');
    });

//******************************************************************************
//* Other Controllers
//******************************************************************************

/** Ops controller for operational api */
\Route::group(['prefix' => 'api/v1', 'middleware' => 'log.dfe-ops-api',],
    function () {
        \Route::controller('ops', 'OpsController');

        \Route::resource('users', 'Ops\\UserController');
        \Route::resource('service-users', 'Ops\\ServiceUserController');
        \Route::resource('servers', 'Ops\\ServerController');
        \Route::resource('clusters', 'Ops\\ClusterController');
        \Route::resource('instances', 'Ops\\InstanceController');
        \Route::resource('mounts', 'Ops\\MountController');
        \Route::resource('app-keys', 'Ops\\AppKeyController');
        \Route::resource('instances', 'Ops\\InstanceController');
        \Route::resource('policies', 'Ops\\PolicyController');
    });

/** Miscellaneous controllers for dashboard functionality */
\Route::controllers([
    'dashboard' => 'DashboardController',
    'settings'  => 'SettingsController',
    'auth'      => 'Auth\\AuthController',
    'password'  => 'Auth\\PasswordController',
]);

//******************************************************************************
//* Testing
//******************************************************************************

\Route::post(
    'form-submit',
    [
        'before' => 'csrf',
        function () {
            //  validation;
        },
    ]
);
