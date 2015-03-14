<?php
namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\Managers\InstanceManager;

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

    /** @inheritdoc */
    const ALIAS_NAME = 'InstanceManager';
    /** @inheritdoc */
    const IOC_NAME = 'instance-manager';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $_serviceClass = 'DreamFactory\\Enterprise\\Services\\Managers\\InstanceManager';

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
                return new InstanceManager( $app );
            }
        );
    }

}
