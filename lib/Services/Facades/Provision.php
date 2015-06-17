<?php
namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Services\Providers\ProvisioningServiceProvider;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ResourceProvisioner getProvisioner(string $name = null)
 * @method static ResourceProvisioner[] getProvisioners()
 * @method static ResourceProvisioner getStorageProvisioner(string $name = null)
 * @method static ResourceProvisioner getDatabaseProvisioner(string $name = null)
 * @method static string getDefaultProvisioner()
 * @method static ResourceProvisioner resolve(string $name)
 * @method static ResourceProvisioner resolveStorage(string $name)
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