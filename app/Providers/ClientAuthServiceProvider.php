<?php
namespace DreamFactory\Enterprise\Console\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Console\Services\ClientAuthService;

class ClientAuthServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const IOC_NAME = 'auth.client';

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
        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new ClientAuthService( $app );
            }
        );
    }
}
