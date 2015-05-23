<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Models\Instance;
use League\Flysystem\Filesystem;

/**
 * Instance storage services
 */
class InstanceStorageService extends BaseService
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_privatePathName = ConsoleDefaults::PRIVATE_PATH_NAME;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Init
     */
    public function boot()
    {
        $this->_privatePathName =
            $this->_cleanPath( config( 'dfe.provisioning.private-path-name', ConsoleDefaults::PRIVATE_PATH_NAME ), false, true );
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     * @param string                                            $append
     *
     * @return string
     */
    public function getRootStoragePath( Instance $instance, $append = null )
    {
        return $instance->getRootStoragePath( $append );
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return string
     */
    public function getStoragePath( Instance $instance )
    {
        return $this->getRootStoragePath( $instance, $instance->instance_id_text );
    }

    /**
     * We want the private path of the instance to point to the user's area. Instances have no "private path" per se.
     *
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return mixed
     */
    public function getPrivatePath( Instance $instance )
    {
        return $this->getStoragePath( $instance ) . DIRECTORY_SEPARATOR . $this->_privatePathName;
    }

    /**
     * We want the private path of the instance to point to the user's area. Instances have no "private path" per se.
     *
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return mixed
     */
    public function getOwnerPrivatePath( Instance $instance )
    {
        return $this->getRootStoragePath( $instance, $this->_privatePathName );
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return string
     */
    public function getSnapshotPath( Instance $instance )
    {
        return
            $this->getOwnerPrivatePath( $instance ) .
            $this->_cleanPath( config( 'dfe.provisioning.snapshot-path-name', ConsoleDefaults::SNAPSHOT_PATH_NAME ) );
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     * @param string                                            $path
     * @param string                                            $tag
     * @param array                                             $options
     *
     * @return Filesystem
     */
    protected function _getMount( $instance, $path, $tag = null, $options = [] )
    {
        if ( !$instance->webServer )
        {
            throw new \InvalidArgumentException( 'No configured web server for instance.' );
        }

        $_mount = $instance->webServer->mount;

        if ( !$_mount )
        {
            throw new \RuntimeException( 'The web server "' . $instance->webServer->server_id_text . '" does not have a mount defined.' );
        }

        return $_mount->getFilesystem( $path, $tag, $options );
    }

    /**
     * Returns the relative root directory of this instance's storage
     *
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     * @param string                                            $tag
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getRootStorageMount( Instance $instance, $tag = null )
    {
        return $this->_getMount( $instance, $this->getRootStoragePath( $instance ), $tag ?: 'storage-root:' . $instance->instance_id_text );
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     * @param string                                            $tag
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getStorageMount( Instance $instance, $tag = null )
    {
        return $this->_getMount( $instance, $this->getStoragePath( $instance ), $tag ?: 'storage:' . $instance->instance_id_text );
    }

    /**
     * Returns the relative root directory of this instance's storage
     *
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     * @param string                                            $tag
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getSnapshotMount( Instance $instance, $tag = null )
    {
        return $this->_getMount( $instance, $this->getSnapshotPath( $instance ), $tag ?: 'snapshots:' . $instance->instance_id_text );
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     * @param string                                            $tag
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getPrivateStorageMount( Instance $instance, $tag = null )
    {
        return $this->_getMount( $instance, $this->getPrivatePath( $instance ), $tag ?: 'private-storage:' . $instance->instance_id_text );
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     * @param string                                            $tag
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getOwnerPrivateStorageMount( Instance $instance, $tag = null )
    {
        return $this->_getMount( $instance, $this->getOwnerPrivatePath( $instance ), $tag ?: 'owner-private-storage:' . $instance->instance_id_text );
    }

    /**
     * @param string $path
     * @param bool   $addSlash     If true (default), a leading slash is added
     * @param bool   $trimTrailing If true, any trailing slashes are removed
     *
     * @return string
     */
    protected function _cleanPath( $path, $addSlash = true, $trimTrailing = false )
    {
        $path = $path ? ( $addSlash ? DIRECTORY_SEPARATOR : null ) . ltrim( $path, ' ' . DIRECTORY_SEPARATOR ) : $path;

        return $trimTrailing ? rtrim( $path, DIRECTORY_SEPARATOR ) : $path;
    }

    /**
     * @return string
     */
    public function getPrivatePathName()
    {
        return $this->_privatePathName;
    }

}