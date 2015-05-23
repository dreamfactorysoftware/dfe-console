<?php namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\StorageMounter;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Database\Exceptions\MountException;
use DreamFactory\Library\Utility\IfSet;
use League\Flysystem\Filesystem;

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
     * @throws \DreamFactory\Enterprise\Database\Exceptions\MountException
     */
    public function mount( $name, $options = [] )
    {
        \Log::debug( 'Mounting "' . $name . '" options: ' . print_r( $options, true ) );

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
        if ( null === ( $_config = config( 'flysystem.connections.' . $_tag ) ) )
        {
            if ( null === ( $_config = config( 'flysystem.connections.' . $name ) ) )
            {
                if ( null === ( $_config = config( 'filesystems.disks.' . $_tag ) ) )
                {
                    if ( null === ( $_config = config( 'filesystems.disks.' . $name ) ) )
                    {
                        if ( empty( $options ) )
                        {
                            throw new MountException( 'No configuration found or specified for mount "' . $name . '".' );
                        }
                    }
                }
            }
            //  Start with a fresh config for this disk
            else if ( $_tag != $name )
            {
                if ( !empty( $_prefix ) )
                {
                    $_prefix = trim( $_prefix, ' ' . DIRECTORY_SEPARATOR );

                    /** @noinspection PhpUndefinedMethodInspection */
                    $_oldPrefix = $_config['path'];

                    if ( false !== strpos( $_oldPrefix, dirname( $_prefix ) ) )
                    {
                        $_oldPrefix = rtrim( str_replace( dirname( $_prefix ), null, $_oldPrefix ), DIRECTORY_SEPARATOR );
                    }

                    $_newPrefix = rtrim( $_oldPrefix, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . $_prefix;

                    /** @noinspection PhpUndefinedMethodInspection */
                    if ( $_oldPrefix != $_newPrefix )
                    {
                        $_config['path'] = $_newPrefix;
                    }
                }
            }
        }

        !isset( $_config['driver'] ) && $_config['driver'] = 'local';
        !isset( $_config['path'] ) && isset( $_config['root'] ) && $_config['path'] = $_config['root'];
        unset( $_config['root'] );

        \Config::set( 'flysystem.connections.' . $_tag, array_merge( $_config, $options ) );

        $this->manage(
            $_tag,
            $_filesystem = \Flysystem::connection( $_tag )
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
    public
    function unmount( $name, $options = [] )
    {
        return $this->unmanage( $name );
    }

}