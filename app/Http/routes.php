<?php
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

//******************************************************************************
//* Ops Controller PRIME
//******************************************************************************

/** Ops controller for operational api */
if (true === config('dfe.enable-console-api', false)) {
    \Route::group([
        'prefix'     => 'api/v1',
        'middleware' => ['log.dfe-ops-api',],
    ],
        function() {
            \Route::controller('ops', 'OpsController');

            \Route::resource('users', 'Ops\UserController');
            \Route::resource('service-users', 'Ops\ServiceUserController');
            \Route::resource('servers', 'Ops\ServerController');
            \Route::resource('clusters', 'Ops\ClusterController');
            \Route::resource('instances', 'Ops\InstanceController');
            \Route::resource('mounts', 'Ops\MountController');
            \Route::resource('app-keys', 'Ops\AppKeyController');
            \Route::resource('instances', 'Ops\InstanceController');
            \Route::resource('limits', 'Ops\LimitController');
        });
}

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

//  Main page
\Route::get(ConsoleDefaults::UI_PREFIX, ['as' => 'home', 'uses' => 'Resources\HomeController@index']);
\Route::get('/home', ['as' => 'home', 'uses' => 'Resources\HomeController@index']);
\Route::get('/', ['as' => 'home', 'uses' => 'Resources\HomeController@index']);
\Route::get('logout', 'Resources\HomeController@logout');

//  FastTrack
if (config('dfe.enable-fast-track')) {
    \Route::get(config('dfe.fast-track-route', '/fast-track'), ['uses' => 'FastTrackController@index']);
    \Route::post(config('dfe.fast-track-route', '/fast-track'), ['uses' => 'FastTrackController@autoRegister']);
    \Route::post('/ops/api/v1/provision-ft', ['uses' => 'OpsController@fastTrackProvision']);
}

//******************************************************************************
//* General Resource Controllers
//******************************************************************************

\Route::group(['prefix' => ConsoleDefaults::UI_PREFIX,],
    function() {
        //  Provisioning settings
        \Route::get('instances/settings', 'Resources\InstanceController@getSettings');
        \Route::post('instances/settings', 'Resources\InstanceController@postSettings');

        //  Specialty routes for UI
        \Route::get('cluster/{clusterId}/instances', 'Resources\ClusterController@getInstances');
        \Route::get('instance/{instanceId}/services', 'Resources\LimitController@getInstanceServices');
        \Route::get('instance/{instanceId}/users', 'Resources\LimitController@getInstanceUsers');
        \Route::get('instance/{instanceId}/admins', 'Resources\LimitController@getInstanceAdmins');
        \Route::delete('instance/{instanceId}/delete', 'Resources\InstanceController@delete');
        \Route::get('reports/kibana', 'Resources\ReportController@getKibana');

        //  UI resource controllers
        \Route::resource('home', 'Resources\HomeController');
        \Route::resource('users', 'Resources\UserController');
        \Route::resource('servers', 'Resources\ServerController');
        \Route::resource('clusters', 'Resources\ClusterController');
        \Route::resource('instances', 'Resources\InstanceController');
        \Route::resource('limits', 'Resources\LimitController');
        \Route::resource('reports', 'Resources\ReportController');
    });

//******************************************************************************
//* All Others
//******************************************************************************

/** Miscellaneous controllers for dashboard functionality */
\Route::controllers([
    'settings' => 'SettingsController',
    'auth'     => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);

/** An endpoint to return the current version of dfe-console */
\Route::get('/version',
    function() {
        return `git rev-parse --verify HEAD`;
    });

/** Login event listener */
\Event::listen('auth.login',
    function() {
        /** @noinspection PhpUndefinedMethodInspection */
        \Auth::user()->update([
            'last_login_date'    => date('c'),
            'last_login_ip_text' => \Request::server('REMOTE_ADDR'),
        ]);
    });

