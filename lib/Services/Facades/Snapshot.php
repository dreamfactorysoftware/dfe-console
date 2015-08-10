<?php
namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Providers\SnapshotServiceProvider;
use Illuminate\Support\Facades\Facade;
use League\Flysystem\Filesystem;

/**
 * @method static array create(string $instanceId, Filesystem $fsDestination = null, int $keepDays = 30)
 * @method static array createFromExports(Instance $instance, array $exports, Filesystem $destination = null, $keepDays = 30)
 * @method static array restore(string $instanceId, string $snapshot, Filesystem $fsDestination = null)
 * @method static mixed downloadFromHash(string $hash)
 * @method static string getRootTrashPath(string $append = null)
 * @method static Filesystem getRootTrashMount(string $append = null)
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