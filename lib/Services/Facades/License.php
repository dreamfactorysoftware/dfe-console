<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Services\Providers\LicenseServerServiceProvider;
use DreamFactory\Library\Utility\Facades\BaseFacade;

/**
 * @method static string getInstallKey()
 * @method static void connect(array $endpoints = [])
 * @method static bool|mixed|\stdClass reportStatistics($data = [])
 * @method static bool|mixed|\stdClass registerAdmin(ServiceUser $serviceUser)
 * @method static bool|mixed|\stdClass registerInstall(ServiceUser $serviceUser)
 * @method static bool|mixed|\stdClass registerInstance(Instance $instance)
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
        return LicenseServerServiceProvider::IOC_NAME;
    }
}
