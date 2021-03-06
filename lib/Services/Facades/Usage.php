<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Library\Utility\Facades\BaseFacade;

/**
 * @method static string generateInstallKey()
 * @method static array getMetrics($options = [])
 * @method static array gatherStatistics()
 *
 * @see \DreamFactory\Enterprise\Services\UsageService
 */
class Usage extends BaseFacade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return UsageServiceProvider::IOC_NAME;
    }
}
