<?php namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\Services\RouteHashingService;

/**
 * Register the route hashing service as a Laravel provider
 */
class RouteHashingServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const IOC_NAME = 'dfe.route-hashing';

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //  Register object into instance container
        $this->singleton(static::IOC_NAME,
            function ($app){
                return new RouteHashingService($app);
            });
    }

}
