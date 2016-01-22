<?php namespace DreamFactory\Enterprise\Console\Providers;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function boot(Dispatcher $dispatcher)
    {
        $dispatcher->mapUsing(function($command) {
            return Dispatcher::simpleMapping($command,
                'DreamFactory\Enterprise\Services\Jobs',
                'DreamFactory\Enterprise\Services\Listeners');
        });
    }

    /** @inheritdoc */
    public function register()
    {
    }
}
