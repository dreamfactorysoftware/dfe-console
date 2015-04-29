<?php namespace DreamFactory\Enterprise\Console\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @var array The event handler mappings for the application.
     */
    protected $listen = [
        'auth.login' => [
            'DreamFactory\Enterprise\Console\Handlers\Events\AuthLoginEventHandler',
        ],
    ];
}
