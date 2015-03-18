<?php namespace DreamFactory\Enterprise\Common\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'DreamFactory\Enterprise\Common\Registrar'
        );

        /**
         * Add the enterprise provider
         */
        $this->app['auth']->extend(
            'enterprise',
            function ()
            {
                return new EnterpriseUserProvider(
                    $this->app['config']['db']->connection(),
                    $this->app['config']['hash'],
                    $this->app['config']['auth.table']
                );
            }
        );

    }

}
