<?php namespace DreamFactory\Enterprise\Console\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $listen = [
        'auth.login' => [
            '\DreamFactory\Enterprise\Console\Listeners\Events\AuthLoginEventHandler',
        ],
    ];
}
