<?php namespace DreamFactory\Enterprise\Console\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function register()
    {
        //  Register our registrar
        $this->app->bind('Illuminate\Contracts\Auth\Registrar', 'DreamFactory\Enterprise\Console\Services\Registrar');
    }
}
