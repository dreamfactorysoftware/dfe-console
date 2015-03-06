<?php
namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\Storage\SnapshotService;

/**
 * Register the snapshot service as a Laravel provider
 *
 * To use the "Snapshot" facade for this provider, add the service provider to
 * your the "providers" array in your config/app.php file:
 *
 *  'providers' => array(
 *
 *      ... Other Providers Above ...
 *      'DreamFactory\Enterprise\Services\Providers\SnapshotServiceProvider',
 *
 *  ),
 */
class SnapshotServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the alias to create
     */
    const ALIAS_NAME = 'Snapshot';
    /**
     * @type string The name of the service in the IoC
     */
    const IOC_NAME = 'dfe.snapshot';

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
        $this->_serviceClass = 'DreamFactory\\Enterprise\\Services\\Storage\\SnapshotService';

        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new $this->_serviceClass;
            }
        );
    }

}
