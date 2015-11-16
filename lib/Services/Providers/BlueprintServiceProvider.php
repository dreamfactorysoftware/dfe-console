<?php namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\BlueprintService;

/**
 * Registers the blueprint service
 */
class BlueprintServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const IOC_NAME = 'dfe.blueprint';

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
        //  Register the manager
        $this->singleton(static::IOC_NAME,
            function ($app){
                return new BlueprintService($app);
            });
    }
}
