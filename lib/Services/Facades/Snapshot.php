<?php namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Providers\SnapshotServiceProvider;
use Illuminate\Support\Facades\Facade;
use League\Flysystem\Filesystem;

/**
 * @method static array create($instanceId, Filesystem $fsDestination = null, $keepDays = 30)
 * @method static array createFromExports(Instance $instance, array $exports, Filesystem $destination = null, $keepDays = 30)
 * @method static array restore($instanceId, $snapshot, Filesystem $fsDestination = null)
 * @method static mixed downloadFromHash($hash)
 */
class Snapshot extends Facade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SnapshotServiceProvider::IOC_NAME;
    }
}
