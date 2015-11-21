<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Providers\LicenseServiceProvider;
use DreamFactory\Library\Utility\Facades\BaseFacade;

/**
 * @method static bool|mixed|\stdClass sendUsageData($data = [])
 * @method static bool|mixed|\stdClass registerAdmin($serviceUser)
 * @method static bool|mixed|\stdClass registerInstance($instance)
 */
class License extends BaseFacade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return LicenseServiceProvider::IOC_NAME;
    }
}
