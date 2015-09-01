<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

/**
 * Provides settings items
 */
class SettingsController extends FactoryController
{
    //********************************************************************************
    //* Methods
    //********************************************************************************

    public function getUsers()
    {
        return $this->renderView('app.users');
    }

    public function getMounts()
    {
        return $this->renderView('app.mounts');
    }

    public function getServers()
    {
        return $this->renderView('app.servers');
    }

    public function getClusters()
    {
        return $this->renderView('app.clusters');
    }

    public function getInstances()
    {
        return $this->renderView('app.instances');
    }

    public function getRoles()
    {
        return $this->renderView('app.roles');
    }

    public function getQuotas()
    {
        return $this->renderView('app.quotas');
    }

}
