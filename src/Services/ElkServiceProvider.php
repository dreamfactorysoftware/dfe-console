<?php
namespace DreamFactory\Enterprise\Console\Services;

use DreamFactory\Enterprise\Console\Providers\Elk;
use Illuminate\Support\ServiceProvider;

/**
 * Gets data from the ELK system
 */
class ElkServiceProvider extends ServiceProvider
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared(
            'elk.service',
            function ( $app )
            {
                return new Elk();
            }
        );
    }
}
