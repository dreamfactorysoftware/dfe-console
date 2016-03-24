<?php namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\DeactivationService;

class DeactivationServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const IOC_NAME = 'dfe.deactivation';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $defer = true;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /** @inheritdoc */
    public function register()
    {
        $this->singleton(static::IOC_NAME,
            function($app) {
                return new DeactivationService($app);
            });
    }
}
