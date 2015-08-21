<?php namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\UsageService;

class UsageServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const IOC_NAME = 'dfe.usage';

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /** @inheritdoc */
    public function register()
    {
        $this->singleton(
            static::IOC_NAME,
            function ($app){
                return new UsageService($app);
            }
        );
    }
}
