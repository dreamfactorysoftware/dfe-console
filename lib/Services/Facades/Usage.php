<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Providers\BlueprintServiceProvider;
use DreamFactory\Library\Utility\Facades\BaseFacade;

/**
 * @method static string generateInstallKey()
 * @method static array getMetrics($options = [])
 * @method static array gatherStatistics()
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
        return BlueprintServiceProvider::IOC_NAME;
    }
}
