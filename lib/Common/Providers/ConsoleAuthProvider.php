<?php namespace DreamFactory\Enterprise\Common\Providers;

use DreamFactory\Enterprise\Common\Auth\ConsoleUserProvider;
use DreamFactory\Library\Fabric\Database\Models\Deploy\ServiceUser;
use Illuminate\Support\ServiceProvider;

class ConsoleAuthProvider extends ServiceProvider
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function boot()
    {
        $this->app['auth']->extend(
            'console',
            function ()
            {
                return new ConsoleUserProvider( $this->app['db']->connection(), $this->app['hash'], config( 'auth.table', 'service_user_t' ) );
            }
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}