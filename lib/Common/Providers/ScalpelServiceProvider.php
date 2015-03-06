<?php
namespace DreamFactory\Enterprise\Common\Providers;

/**
 * Register the scalpel service into the $app ioc @ 'scalpel'
 *
 * To use the "Scalpel" facade for this provider, add the service provider to
 * your the "providers" array in your config/app.php file:
 *
 *  'providers' => array(
 *
 *      ... Other Providers Above ...
 *      'DreamFactory\Enterprise\Services\Providers\ScalpelServiceProvider',
 *
 *  ),
 */
class ScalpelServiceProvider extends BaseServiceProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const IOC_NAME = 'scalpel';
    /** @inheritdoc */
    const ALIAS_NAME = 'Scalpel';

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
        $this->_serviceClass = 'DreamFactory\\Enterprise\\Common\\Services\\ScalpelService';

        //  Register object into instance container
        $this->singleton(
            static::IOC_NAME,
            function ( $app )
            {
                return new $this->_serviceClass( $app );
            }
        );
    }
}
