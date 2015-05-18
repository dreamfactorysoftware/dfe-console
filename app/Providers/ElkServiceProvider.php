<?php
namespace DreamFactory\Enterprise\Console\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Console\Services\ElkService;

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
        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new ElkService( $app );
            }
        );
    }
}
