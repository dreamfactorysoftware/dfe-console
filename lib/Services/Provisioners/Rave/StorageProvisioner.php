<?php
namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Contracts\PrivatePathAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * DreamFactory Enterprise(tm) and Services Platform File System
 *
 * The default functionality (static::$partitioned is set to TRUE) of this resolver is to provide partitioned
 * layout paths for the hosted storage area. The structure generated is as follows:
 *
 * /mount_point                             <----- Mount point/absolute path of storage area (i.e. "/")
 *      /storage                            <----- Root directory of hosted storage (i.e. "/data/storage")
 *          /zone                           <----- The storage zones (ec2.us-east-1a, ec2.us-west-1b, local, etc.)
 *              /[00-ff]                    <----- The first two bytes of hashes within (the partition)
 *                  /owner-hash
 *                      /.private           <----- owner private storage root
 *                      /instance-hash      <----- Instance storage root
 *                          /.private       <----- Instance private storage root
 *
 * Example paths:
 *
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/.private
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/applications
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/plugins
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private/.cache
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private/config
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private/scripts
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private/scripts.user
 */
class StorageProvisioner implements ResourceProvisioner, PrivatePathAware
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Partitioning hash type
     */
    protected $_algorithm = 'sha256';
    /**
     * @type string The instances's private path
     */
    protected $_privatePath = null;
    /**
     * @type string The user's private path
     */
    protected $_ownerPrivatePath = null;
    /**
     * @type bool Indicates if the storage I govern is hosted or standalone
     */
    protected $_hostedStorage = true;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /** @inheritdoc */
    public function provision( $request, $options = [] )
    {
        //  Make structure
        $this->_createInstanceStorage( $request->getInstance(), $request->getStorage(), $options );
    }

    /** @inheritdoc */
    public function deprovision( $request, $options = [] )
    {
        //  '86 structure
        $this->_removeInstanceStorage( $request->getInstance(), $request->getStorage(), $options );
    }

    /**
     * Create storage structure in $filesystem
     *
     * @param Instance   $instance
     * @param Filesystem $filesystem
     * @param array      $options
     */
    protected function _createInstanceStorage( $instance, $filesystem, $options = [] )
    {
        //  Wipe existing stuff
        $this->_privatePath = $this->_ownerPrivatePath = null;

        //  Based on the instance, determine the root, partition and hash
        list( $_zone, $_partition, $_rootHash ) = $this->_resolveStructure( $instance );

        //  Storage root path
        $_rootPath = $this->_makeRootPath( $_zone, $_partition, $_rootHash );

        //  The instance's base storage path
        $_instanceRootPath = $this->_makeRootPath( $_zone, $_partition, $_rootHash, $instance->instance_id_text );

        //  Build privates....
        $_privateName = trim( config( 'dfe.provisioning.private-base-path', '.private' ), DIRECTORY_SEPARATOR . ' ' );

        //  The instance's private path
        $_privatePath = $_instanceRootPath . DIRECTORY_SEPARATOR . $_privateName;

        //  The user's private path. Same as instance's when non-hosted
        $_ownerPrivatePath = $_rootPath . DIRECTORY_SEPARATOR . $_privateName;

        //  Make sure everything exists
        !$filesystem->exists( $_rootPath ) && $filesystem->makeDirectory( $_rootPath );
        !$filesystem->exists( $_privatePath ) && $filesystem->makeDirectory( $_privatePath );
        $this->_hostedStorage && !$filesystem->exists( $_ownerPrivatePath ) && $filesystem->makeDirectory( $_ownerPrivatePath );

        //  Now ancillary sub-directories
        foreach ( config( 'dfe.provisioning.public-paths', [] ) as $_path )
        {
            if ( !$filesystem->exists( $_check = $_instanceRootPath . DIRECTORY_SEPARATOR . $_path ) )
            {
                $filesystem->exists( $_check ) && $filesystem->makeDirectory( $_check );
            }
        }

        foreach ( config( 'dfe.provisioning.private-paths', [] ) as $_path )
        {
            if ( !$filesystem->exists( $_check = $_privatePath . DIRECTORY_SEPARATOR . $_path ) )
            {
                $filesystem->exists( $_check ) && $filesystem->makeDirectory( $_check );
            }
        }

        foreach ( config( 'dfe.provisioning.owner-private-paths', [] ) as $_path )
        {
            if ( !$filesystem->exists( $_check = $_ownerPrivatePath . DIRECTORY_SEPARATOR . $_path ) )
            {
                $filesystem->exists( $_check ) && $filesystem->makeDirectory( $_check );
            }
        }

        \Log::debug( '    * provisioner: instance storage created' );
        \Log::debug( '      * private path:       ' . $_privatePath );
        \Log::debug( '      * owner private path: ' . $_ownerPrivatePath );

        $this->_privatePath = $_privatePath;
        $this->_ownerPrivatePath = $_ownerPrivatePath;
    }

    /**
     * Delete storage of an instance
     *
     * @param Instance   $instance
     * @param Filesystem $filesystem
     * @param array      $options
     */
    protected function _removeInstanceStorage( $instance, $filesystem, $options = [] )
    {
        list( $_zone, $_partition, $_rootHash ) = $this->_resolveStructure( $instance );
        $_storagePath = $this->_makeRootPath( $_zone, $_partition, $_rootHash, $instance->instance_id_text );

        //  I'm not sure how hard this tries to delete the directory
        $filesystem->exists( $_storagePath ) && $filesystem->deleteDirectory( $_storagePath );

        \Log::debug( '    * provisioner: instance storage removed' );
    }

    /**
     * Based on the requirements, resolve the base components of the storage area
     *
     * @param Instance $instance
     *
     * @return array
     */
    protected function _resolveStructure( Instance $instance )
    {
        //  Non-hosted has no structure, just storage
        if ( !$this->_hostedStorage )
        {
            return array(null, null, null);
        }

        $_rootHash = hash( $this->_algorithm, $instance->user->storage_id_text );
        $_partition = substr( $_rootHash, 0, 2 );

        $_zone = null;

        switch ( config( 'dfe.provisioning.storage-zone-type' ) )
        {
            case 'dynamic':
                switch ( $instance->guest_location_nbr )
                {
                    case GuestLocations::AMAZON_EC2:
                    case GuestLocations::DFE_CLUSTER:
                        if ( file_exists( '/usr/bin/ec2metadata' ) )
                        {
                            $_zone = str_replace( 'availability-zone: ', null, `/usr/bin/ec2metadata | grep zone` );
                        }
                        break;
                }
                break;

            case 'static':
                $_zone = config( 'dfe.provisioning.static-zone-name' );
                break;
        }

        if ( empty( $_zone ) || empty( $_partition ) )
        {
            throw new \RuntimeException( 'Zone and/or partition unknown. Cannot provision storage.' );
        }

        $_structure = [$_zone, $_partition, $_rootHash];

        return $_structure;
    }

    /**
     * @param string $zone
     * @param string $partition
     * @param string $rootHash
     * @param string $instanceId Optional instance id to append to path
     *
     * @return string
     */
    protected function _makeRootPath( $zone, $partition, $rootHash, $instanceId = null )
    {
        $_rootPath = $this->_hostedStorage
            ? DIRECTORY_SEPARATOR .
            $zone .
            DIRECTORY_SEPARATOR .
            $partition .
            DIRECTORY_SEPARATOR .
            $rootHash .
            ( $instanceId ? DIRECTORY_SEPARATOR . $instanceId : null )
            : null;

        return $_rootPath;
    }

    /** @inheritdoc */
    public function getPrivatePath( $append = null )
    {
        return $this->_privatePath;
    }

    /** @inheritdoc */
    public function getOwnerPrivatePath( $append = null )
    {
        //  I hate doing this, but it will make this service more streamlined...
        return $this->_hostedStorage ? $this->_ownerPrivatePath : $this->getPrivatePath();
    }

    /**
     * @return boolean
     */
    public function isHostedStorage()
    {
        return $this->_hostedStorage;
    }

    /**
     * @param boolean $hostedStorage
     *
     * @return $this
     */
    public function setHostedStorage( $hostedStorage )
    {
        $this->_hostedStorage = $hostedStorage;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->_algorithm;
    }

    /**
     * @param string $algorithm
     *
     * @return $this
     */
    public function setAlgorithm( $algorithm )
    {
        $this->_algorithm = $algorithm;

        return $this;
    }

}