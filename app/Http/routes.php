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

        \Route::get(ConsoleDefaults::UI_PREFIX . '/cluster/{clusterId}/instances',
            'Resources\\ClusterController@getInstances');
        \Route::get(ConsoleDefaults::UI_PREFIX . '/instance/{instanceId}/services',
            'Resources\\LimitController@getInstanceServices');
        \Route::get(ConsoleDefaults::UI_PREFIX . '/instance/{instanceId}/users',
            'Resources\\LimitController@getInstanceUsers');
    });

//******************************************************************************
//* Resource Controllers
//******************************************************************************

\Route::group([
    'prefix'     => ConsoleDefaults::UI_PREFIX,
    'namespace'  => 'Resources',
    'middleware' => 'auth',
],
    function () {
        \Route::resource('users', 'UserController');
        \Route::resource('servers', 'ServerController');
        \Route::resource('clusters', 'ClusterController');
        \Route::resource('instances', 'InstanceController');
        \Route::resource('limits', 'LimitController');
        \Route::resource('reports', 'ReportController');
    });

//******************************************************************************
//* Other Controllers
//******************************************************************************

/** Ops controller for operational api */
if (true === config('dfe.enable-console-api', false)) {
    \Route::group([
        'prefix'     => 'api/v1',
        'middleware' => 'log.dfe-ops-api',
    ],
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
            \Route::resource('limits', 'Ops\\LimitController');
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

\Route::post('form-submit',
    [
        'before' => 'csrf',
        function () {
            //  validation;
        },
    ]);
