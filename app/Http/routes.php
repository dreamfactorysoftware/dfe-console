<?php
//******************************************************************************
//* Implicit Controllers
//******************************************************************************

//  Main page
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

\Route::group(['middleware' => 'auth'],
    function () {
        \Route::get(ConsoleDefaults::UI_PREFIX, ['as' => '/', 'uses' => 'HomeController@index']);
        \Route::get(ConsoleDefaults::UI_PREFIX . '/home', ['as' => 'home', 'uses' => 'HomeController@index']);
        \Route::get(ConsoleDefaults::UI_PREFIX . '/cluster/instances/{clusterId}',
            'Resources\\ClusterController@getInstances');
    });

//******************************************************************************
//* Resource Controllers
//******************************************************************************

\Route::group(
    [
        'prefix'     => ConsoleDefaults::UI_PREFIX,
        'namespace'  => 'Resources',
        'middleware' => 'auth',
    ],
    function () {
        \Route::resource('users', 'UserController');
        \Route::resource('servers', 'ServerController');
        \Route::resource('clusters', 'ClusterController');
        \Route::resource('instances', 'InstanceController');
        \Route::resource('policies', 'PolicyController');
        \Route::resource('reports', 'ReportController');
    });

//******************************************************************************
//* Other Controllers
//******************************************************************************

/** Ops controller for operational api */
if (true === config('dfe.enable-console-api', false)) {
    \Route::group(
        [
            'prefix'     => 'api/v1',
            'middleware' => 'log.dfe-ops-api',
        ],
        function () {
            \Route::controller('ops', 'OpsController');

            \Route::group(
                [
                    'namespace' => 'Ops',
                ],
                function () {
                    \Route::resource('users', 'UserController');
                    \Route::resource('service-users', 'ServiceUserController');
                    \Route::resource('servers', 'ServerController');
                    \Route::resource('clusters', 'ClusterController');
                    \Route::resource('instances', 'InstanceController');
                    \Route::resource('mounts', 'MountController');
                    \Route::resource('app-keys', 'AppKeyController');
                    \Route::resource('instances', 'InstanceController');
                    \Route::resource('policies', 'PolicyController');
                });
        });
}

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
