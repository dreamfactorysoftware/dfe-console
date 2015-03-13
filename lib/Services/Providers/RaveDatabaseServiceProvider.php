<?php
namespace DreamFactory\Enterprise\Services\Providers;

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\RaveDatabaseService;

/**
 * Register the RAVE database service
 */
class RaveDatabaseServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const IOC_NAME = 'dfe.db.rave';
    /** @inheritdoc */
    const ALIAS_NAME = false;

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
        //  Register object into instance container
        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new RaveDatabaseService( $app );
            }
        );
    }
}
