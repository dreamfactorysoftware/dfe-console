<?php
namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Providers\InstanceServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \DreamFactory\Enterprise\Services\Managers\InstanceManager registerInstances(array $instances)
 * @method static \DreamFactory\Enterprise\Services\Managers\InstanceManager registerInstance(string $tag, Instance
 *         $instance)
 * @method static \DreamFactory\Enterprise\Services\Managers\InstanceManager unregisterInstance(string $tag, Instance
 *         $instance)
 * @method static Instance getInstance(string $tag)
 * @method static Instance make(string $instanceName, $options = [])
 * @method static Filesystem getFilesystem(Instance $instance)
 * @method static Filesystem getPrivateFilesystem(Instance $instance)
 */
class InstanceManager extends Facade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return InstanceServiceProvider::IOC_NAME;
    }
}