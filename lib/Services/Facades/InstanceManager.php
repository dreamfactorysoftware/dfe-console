<?php
namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Providers\InstanceManagerProvider;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Instance make( string $instanceName, array $options = [] )
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
        return InstanceManagerProvider::IOC_NAME;
    }
}