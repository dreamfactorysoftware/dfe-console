<?php
namespace DreamFactory\Enterprise\Services\Facades;

use DreamFactory\Enterprise\Services\Managers\MountManager;
use DreamFactory\Enterprise\Services\Providers\MountServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Filesystem mount( string $name, $options = [] )
 * @method static MountManager unmount( string $name, $options = [] )
 */
class Mounter extends Facade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MountServiceProvider::IOC_NAME;
    }
}