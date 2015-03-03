<?php
namespace DreamFactory\Enterprise\Console\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Console\Services\Elk;

/**
 * Gets data from the ELK system
 */
class ElkServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const IOC_NAME = 'dfe.elk';
    /**
     * @type string
     */
    const ALIAS_NAME = 'Elk';

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->_serviceClass = 'DreamFactory\\Enterprise\\Console\\Services\\Elk';

        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new Elk();
            }
        );
    }
}
