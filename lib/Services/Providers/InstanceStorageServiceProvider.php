<?php
namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\InstanceStorageService;

/**
 * Registers the instance storage service
 */
class InstanceStorageServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const IOC_NAME = 'dfe.instance-storage';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $_serviceClass = 'DreamFactory\\Enterprise\\Services\\InstanceStorageService';

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
        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new InstanceStorageService( $app );
            }
        );
    }

}
