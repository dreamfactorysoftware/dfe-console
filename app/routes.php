<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

//******************************************************************************
//* Implicit Controllers
//******************************************************************************

/** @noinspection PhpUndefinedMethodInspection */
Route::controller( 'app', 'AppController' );
/** @noinspection PhpUndefinedMethodInspection */
Route::group(
    array('prefix' => 'api/v1'),
    function ()
    {
        Route::resource( 'servers', 'ServerController' );
        Route::resource( 'clusters', 'ClusterController' );
        Route::resource( 'instances', 'InstanceController' );
        Route::resource( 'roles', 'RoleController' );
        Route::resource( 'users', 'UserController' );
    }
);
/** @noinspection PhpUndefinedMethodInspection */
Route::get(
    '/',
    function ()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return View::make(
            'app.dashboard',
            array('_trail' => null, '_active' => array('instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0))
        );
    }
);