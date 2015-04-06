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
        }

        //  See if we have a disk
        $_config = config( 'filesystems.disks.' . $name );

        if ( empty( $_config ) )
        {
            if ( empty( $options ) )
            {
                throw new \InvalidArgumentException( 'No configuration found or specified for mount "' . $name . '".' );
            }

            \Config::set( 'filesystems.disks.' . $name, $options );
        }

        $this->manage(
            $name,
            $_filesystem = \Storage::disk( $name )
        );

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