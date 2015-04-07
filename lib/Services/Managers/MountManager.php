<?php namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\StorageMounter;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Filesystem\FilesystemAdapter;

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
     * @return FilesystemAdapter
     */
    public function mount( $name, $options = [] )
    {
        $_tag = IfSet::get( $options, 'tag', $name );

        try
        {
            return $this->resolve( $_tag );
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
            $_tag,
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