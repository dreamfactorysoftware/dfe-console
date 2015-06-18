<?php
namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\SnapshotService;

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
        $this->singleton(
            static::IOC_NAME,
            function ($app){
                return new SnapshotService($app);
            }
        );
    }

}
