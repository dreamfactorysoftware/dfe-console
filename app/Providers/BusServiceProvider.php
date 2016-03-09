<?php namespace DreamFactory\Enterprise\Console\Providers;

use Collective\Bus\Dispatcher;
use Collective\Bus\BusServiceProvider as CollectiveBusServiceProvider;

class BusServiceProvider extends CollectiveBusServiceProvider
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param \Collective\Bus\Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher)
    {
        $dispatcher->mapUsing(function($command) {
            return Dispatcher::simpleMapping($command,
                'DreamFactory\Enterprise\Services\Jobs',
                'DreamFactory\Enterprise\Services\Listeners');
        });
    }
}
