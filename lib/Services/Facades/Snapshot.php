<?php
namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Providers\SnapshotServiceProvider;
use Illuminate\Support\Facades\Facade;
use League\Flysystem\Filesystem;

/**
 * @method static array create( string $instanceId, Filesystem $fsSource, Filesystem $fsDestination, int $keepDays = 30 )
 * @method static array restore( string $instanceId, string $snapshot, Filesystem $fsDestination )
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