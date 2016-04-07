<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Common\Contracts\PortableData;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use DreamFactory\Enterprise\Services\Jobs\ExportJob;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;
use DreamFactory\Enterprise\Services\Providers\ProvisioningServiceProvider;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ResourceProvisioner getProvisioner($name = null)
 * @method static ResourceProvisioner[] getProvisioners()
 * @method static ResourceProvisioner getStorageProvisioner($name = null)
 * @method static ResourceProvisioner getDatabaseProvisioner($name = null)
 * @method static string getDefaultProvisioner()
 * @method static array|PortableData[] getPortableServices($name = null)
 * @method static ResourceProvisioner resolve($name)
 * @method static ResourceProvisioner provision(ProvisionJob $job)
 * @method static ResourceProvisioner deprovision(DeprovisionJob $job)
 * @method static ResourceProvisioner selfDestruct($instanceId, $days = null, $extends = null, $dryRun = false)
 * @method static array import(ImportJob $job)
 * @method static array export(ExportJob $job)
 * @method static ResourceProvisioner resolveStorage($name)
 */
class Provision extends Facade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ProvisioningServiceProvider::IOC_NAME;
    }
}
