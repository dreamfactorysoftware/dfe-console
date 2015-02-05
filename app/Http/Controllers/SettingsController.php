<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;

/**
 * Provides settings items
 */
class SettingsController extends FactoryController
{
    //********************************************************************************
    //* Methods
    //********************************************************************************

    public function __construct()
    {
        $this->_active = array();
    }

    public function getUsers()
    {
        return View::make( 'app.users' );
    }

    public function getServers()
    {
        return View::make( 'app.servers' );
    }

    public function getClusters()
    {
        return View::make( 'app.clusters' );
    }

    public function getInstances()
    {
        return View::make( 'app.instances' );
    }

    public function getRoles()
    {
        return View::make( 'app.roles' );
    }

    public function getQuotas()
    {
        return View::make( 'app.quotas' );
    }

}
