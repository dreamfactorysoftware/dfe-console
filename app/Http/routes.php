<?php
//******************************************************************************
//* Ops Controller PRIME
//******************************************************************************

/** Ops controller for operational api */
if (true === config('dfe.enable-console-api', false)) {
    \Route::group([
        'prefix'     => 'api/v1',
        'middleware' => 'log.dfe-ops-api',
    ],
        function (){
            \Route::controller('ops', 'OpsController');

            \Route::resource('user', DreamFactory\Enterprise\Console\Http\Controllers\Ops\UserController::class);
            \Route::resource('service-user',
                DreamFactory\Enterprise\Console\Http\Controllers\Ops\ServiceUserController::class);
            \Route::resource('server', DreamFactory\Enterprise\Console\Http\Controllers\Ops\ServerController::class);
            \Route::resource('cluster', DreamFactory\Enterprise\Console\Http\Controllers\Ops\ClusterController::class);
            \Route::resource('instance',
                DreamFactory\Enterprise\Console\Http\Controllers\Ops\InstanceController::class);
            \Route::resource('mount', DreamFactory\Enterprise\Console\Http\Controllers\Ops\MountController::class);
            \Route::resource('app-key', DreamFactory\Enterprise\Console\Http\Controllers\Ops\AppKeyController::class);
            \Route::resource('instance',
                DreamFactory\Enterprise\Console\Http\Controllers\Ops\InstanceController::class);
            \Route::resource('limit', DreamFactory\Enterprise\Console\Http\Controllers\Ops\LimitController::class);
        });
}

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

//  Main page
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

\Route::group(['middleware' => 'auth'],
    function (){
        \Route::get(ConsoleDefaults::UI_PREFIX, ['as' => 'home', 'uses' => 'Resources\\HomeController@index']);
        \Route::get('/home', ['as' => 'home', 'uses' => 'Resources\\HomeController@index']);
        \Route::get('/', ['as' => 'home', 'uses' => 'Resources\\HomeController@index']);
        \Route::get(ConsoleDefaults::UI_PREFIX . '/cluster/{clusterId}/instances',
            'Resources\\ClusterController@getInstances');
        \Route::get(ConsoleDefaults::UI_PREFIX . '/instance/{instanceId}/services',
            'Resources\\LimitController@getInstanceServices');
        \Route::get(ConsoleDefaults::UI_PREFIX . '/instance/{instanceId}/users',
            'Resources\\LimitController@getInstanceUsers');
    });

//******************************************************************************
//* General Resource Controllers
//******************************************************************************

\Route::group([
    'prefix'     => ConsoleDefaults::UI_PREFIX,
    'middleware' => 'auth',
],
    function (){
        \Route::resource('home', DreamFactory\Enterprise\Console\Http\Controllers\Resources\HomeController::class);
        \Route::resource('users', DreamFactory\Enterprise\Console\Http\Controllers\Resources\UserController::class);
        \Route::resource('servers', DreamFactory\Enterprise\Console\Http\Controllers\Resources\ServerController::class);
        \Route::resource('clusters',
            DreamFactory\Enterprise\Console\Http\Controllers\Resources\ClusterController::class);
        \Route::resource('instances',
            DreamFactory\Enterprise\Console\Http\Controllers\Resources\InstanceController::class);
        \Route::resource('limits',
            DreamFactory\Enterprise\Console\Http\Controllers\Resources\LimitController::class);
        \Route::resource('reports', DreamFactory\Enterprise\Console\Http\Controllers\Resources\ReportController::class);
    });

//******************************************************************************
//* All Others
//******************************************************************************

/** Miscellaneous controllers for dashboard functionality */
\Route::controllers([
    'dashboard' => 'DashboardController',
    'settings'  => 'SettingsController',
    'auth'      => 'Auth\\AuthController',
    'password'  => 'Auth\\PasswordController',
]);

/** An endpoint to return the current version of dfe-console */
\Route::get('/version',
    function (){
        return `git rev-parse --verify HEAD`;
    });
