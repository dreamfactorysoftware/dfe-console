<?php
namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;

/**
 * Register the hosting service
 */
class ProvisioningServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the service in the IoC
     */
    const IOC_NAME = 'dfe.provisioning';

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
        $this->_serviceClass = 'DreamFactory\\Enterprise\\Services\\Provisioners\\DreamFactoryRave';

        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new $this->_serviceClass( $app );
            }
        );
    }

}
