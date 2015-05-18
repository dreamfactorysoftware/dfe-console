<?php
namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Providers\InstanceStorageServiceProvider;
use DreamFactory\Enterprise\Database\Models\Deploy\Instance;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getStoragePath( Instance $instance )
 * @method static string getSnapshotPath( Instance $instance )
 * @method static string getPrivatePathName()
 * @method static string getPrivatePath( Instance $instance )
 * @method static string getOwnerPrivatePath( Instance $instance )
 * @method static FilesystemAdapter getRootStorageMount( Instance $instance, string $path = null, string $tag = 'root-storage-mount' )
 * @method static FilesystemAdapter getStorageMount( Instance $instance, string $tag = 'root-storage-mount' )
 * @method static FilesystemAdapter getSnapshotMount( Instance $instance, string $tag = 'snapshot-mount' )
 * @method static FilesystemAdapter getPrivateStorageMount( Instance $instance, string $tag = 'private-storage' )
 * @method static FilesystemAdapter getOwnerPrivateStorageMount( Instance $instance, string $tag = 'owner-private-storage' )
 */
class InstanceStorage extends Facade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return InstanceStorageServiceProvider::IOC_NAME;
    }
}