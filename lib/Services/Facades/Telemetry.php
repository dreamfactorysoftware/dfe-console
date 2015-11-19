<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Contracts\ProvidesTelemetry;
use DreamFactory\Enterprise\Services\Providers\TelemetryServiceProvider;
use DreamFactory\Library\Utility\Facades\BaseFacade;

/**
 * @method static ProvidesTelemetry registerProvider($name, $provider)
 * @method static bool unregisterProvider($name)
 * @method static ProvidesTelemetry make($name)
 * @method static array getTelemetry($providers = null, $start = null, $end = null, $store = true);
 */
class Telemetry extends BaseFacade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TelemetryServiceProvider::IOC_NAME;
    }
}
