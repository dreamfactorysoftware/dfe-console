<?php
namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\Managers\MountManager;

/**
 * Register the storage mount service as a Laravel provider
 */
class MountServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the service in the IoC
     */
    const IOC_NAME = 'dfe.mount';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $_serviceClass = 'DreamFactory\\Enterprise\\Services\\Managers\\MountManager';

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
        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new MountManager( $app );
            }
        );
    }

}
