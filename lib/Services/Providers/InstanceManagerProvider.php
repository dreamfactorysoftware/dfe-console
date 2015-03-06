<?php
namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\Storage\SnapshotService;

/**
 * Registers the instance manager as a service
 *
 * To use the "InstMan" facade for this provider, add the service provider to
 * your the "providers" array in your config/app.php file:
 *
 *  'providers' => array(
 *
 *      ... Other Providers Above ...
 *      'DreamFactory\Enterprise\Services\Providers\InstanceManagerProvider',
 *
 *  ),
 */
class InstanceManagerProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the alias to create
     */
    const ALIAS_NAME = 'InstanceManager';
    /**
     * @type string The name of the service in the IoC
     */
    const IOC_NAME = 'dfe.instance-manager';

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
        $this->_serviceClass = 'DreamFactory\\Enterprise\\Services\\Managers\\InstanceManager';

        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new $this->_serviceClass;
            }
        );
    }

}
