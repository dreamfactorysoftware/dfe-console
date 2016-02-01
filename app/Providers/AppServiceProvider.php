<?php namespace DreamFactory\Enterprise\Console\Providers;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Boot the application
     */
    public function boot()
    {
        //  Share these values across all views
        \View::share('prefix', config('dfe.ui.prefix', ConsoleDefaults::UI_PREFIX));
        \View::share('instance_domain', config('provisioning.default-domain'));
    }

    /** @inheritdoc */
    public function register()
    {
        //  Register our registrar
        $this->app->bind('Illuminate\Contracts\Auth\Registrar', 'DreamFactory\Enterprise\Console\Services\Registrar');
    }
}
