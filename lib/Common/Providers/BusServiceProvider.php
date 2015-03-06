<?php namespace DreamFactory\Enterprise\Common\Providers;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Bus\Dispatcher $dispatcher
     *
     * @return void
     */
    public function boot( Dispatcher $dispatcher )
    {
        $dispatcher->mapUsing(
            function ( $command )
            {
                return Dispatcher::simpleMapping(
                    $command,
                    'DreamFactory\Enterprise\Common\Commands',
                    'DreamFactory\Enterprise\Common\Handlers\Commands'
                );
            }
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
