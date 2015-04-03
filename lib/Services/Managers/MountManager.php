<?php namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\StorageMounter;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use Illuminate\Contracts\Filesystem\Filesystem;

class MountManager extends BaseManager implements StorageMounter
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Mount the filesystem "$name" as defined in "config/filesystems.php"
     *
     * @param string $name
     * @param array  $options
     *
     * @return Filesystem
     */
    public function mount( $name, $options = [] )
    {
        try
        {
            return $this->resolve( $name );
        }
        catch ( \InvalidArgumentException $_ex )
        {
            $this->manage(
                $name,
                $_filesystem = \Storage::disk( $name )
            );
        }

        return $_filesystem;
    }

    /**
     * Unmount the filesystem "$name" as defined in "config/filesystems.php"
     *
     * @param string $name
     * @param array  $options
     *
     * @return StorageMounter
     */
    public function unmount( $name, $options = [] )
    {
        return $this->unmanage( $name );
    }
}