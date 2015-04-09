<?php namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\StorageMounter;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Library\Fabric\Database\Exceptions\MountException;
use DreamFactory\Library\Utility\IfSet;

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
     * @return \Illuminate\Filesystem\FilesystemAdapter
     * @throws \DreamFactory\Library\Fabric\Database\Exceptions\MountException
     */
    public function mount( $name, $options = [] )
    {
        $_tag = str_replace( '.', '-', IfSet::get( $options, 'tag', $name ) );
        $_prefix = IfSet::get( $options, 'prefix' );

        try
        {
            return $this->resolve( $_tag );
        }
        catch ( \InvalidArgumentException $_ex )
        {
        }

        //  See if we have a disk
        if ( null === ( $_config = config( 'filesystems.disks.' . $_tag ) ) )
        {
            if ( null === ( $_config = config( 'filesystems.disks.' . $name ) ) )
            {
                if ( empty( $options ) )
                {
                    throw new MountException( 'No configuration found or specified for mount "' . $name . '".' );
                }

                \Config::set( 'filesystems.disks.' . $_tag, $options );
            }
            //  Start with a fresh config for this disk
            else if ( $_tag != $name )
            {
                if ( !empty( $_prefix ) )
                {
                    $_prefix = trim( $_prefix, ' ' . DIRECTORY_SEPARATOR );

                    /** @noinspection PhpUndefinedMethodInspection */
                    $_oldPrefix = $_config['root'];

                    if ( false !== strpos( $_oldPrefix, dirname( $_prefix ) ) )
                    {
                        $_oldPrefix = rtrim( str_replace( dirname( $_prefix ), null, $_oldPrefix ), DIRECTORY_SEPARATOR );
                    }

                    $_newPrefix = rtrim( $_oldPrefix, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . $_prefix;

                    /** @noinspection PhpUndefinedMethodInspection */
                    if ( $_oldPrefix != $_newPrefix )
                    {
                        $_config['root'] = $_newPrefix;
                    }
                }

                \Config::set( 'filesystems.disks.' . $_tag, $_config );
            }
        }

        $this->manage(
            $_tag,
            $_filesystem = \Storage::disk( $_tag )
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