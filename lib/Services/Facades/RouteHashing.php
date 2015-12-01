<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Providers\RouteHashingServiceProvider;
use DreamFactory\Library\Utility\Facades\BaseFacade;

/**
 * @method static string create($pathToHash, $keepDays = 30)
 * @method static string resolve($hashToResolve)
 * @method static int expireFiles($fsToCheck)
 */
class RouteHashing extends BaseFacade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return RouteHashingServiceProvider::IOC_NAME;
    }
}
