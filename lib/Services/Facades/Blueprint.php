<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\BlueprintService;
use DreamFactory\Enterprise\Services\Providers\BlueprintServiceProvider;
use DreamFactory\Library\Utility\Facades\BaseFacade;

/**
 * @method static BlueprintService make($instanceId, $options = [])
 */
class Blueprint extends BaseFacade
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
