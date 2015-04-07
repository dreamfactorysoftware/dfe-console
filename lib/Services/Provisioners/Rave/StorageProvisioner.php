<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Contracts\PrivatePathAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

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
     * @type string The instance's private path
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
    /**
     * @type array The map of storage segments
     */
    protected $_storageMap = [];

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /** @inheritdoc */
    public function provision( $request, $options = [] )
    {
        //  Make structure
        $this->_createInstanceStorage( $request, $options );
    }

    /** @inheritdoc */
    public function deprovision( $request, $options = [] )
    {
        //  '86 structure
        $this->_removeInstanceStorage( $request, $options );
    }

    /**
     * Create storage structure in $filesystem
     *
     * @param ProvisioningRequest $request
     * @param array               $options
     */
    protected function _createInstanceStorage( $request, $options = [] )
    {
        $_privateName = trim( config( 'dfe.provisioning.private-base-path', '.private' ), DIRECTORY_SEPARATOR . ' ' );

        //  Wipe existing stuff
        $this->_privatePath = $this->_ownerPrivatePath = null;

        $_instance = $request->getInstance();
        $this->_storageMap = $_instance->getStorageMap();

        $_filesystem = $request->getStorage();

        //******************************************************************************
        //* Directories are all relative to the request's storage file system
        //******************************************************************************

        //  Storage root path
        $_rootPath = null;

        //  The instance's base storage path
        $_instanceRootPath = $_instance->instance_id_text;

        //  The instance's private path
        $_privatePath = $_instanceRootPath . DIRECTORY_SEPARATOR . $_privateName;

        //  The user's private path. Same as instance's when non-hosted
        $_ownerPrivatePath = $_privateName;

        //  Make sure everything exists
        !$_filesystem->exists( $_rootPath ) && $_filesystem->makeDirectory( $_rootPath );
        !$_filesystem->exists( $_privatePath ) && $_filesystem->makeDirectory( $_privatePath );
        !$_filesystem->exists( $_ownerPrivatePath ) && $_filesystem->makeDirectory( $_ownerPrivatePath );

        //  Now ancillary sub-directories
        foreach ( config( 'dfe.provisioning.public-paths', [] ) as $_path )
        {
            !$_filesystem->exists( $_check = $_instanceRootPath . DIRECTORY_SEPARATOR . $_path ) && $_filesystem->makeDirectory( $_check );
        }

        foreach ( config( 'dfe.provisioning.private-paths', [] ) as $_path )
        {
            !$_filesystem->exists( $_check = $_privatePath . DIRECTORY_SEPARATOR . $_path ) && $_filesystem->makeDirectory( $_check );
        }
        foreach ( config( 'dfe.provisioning.owner-private-paths', [] ) as $_path )
        {
            !$_filesystem->exists( $_check = $_ownerPrivatePath . DIRECTORY_SEPARATOR . $_path ) && $_filesystem->makeDirectory( $_check );
        }

        \Log::debug( '    * provisioner: instance storage created' );
        \Log::debug( '      * root path:          ' . $_rootPath );
        \Log::debug( '      * private path:       ' . $_privatePath );
        \Log::debug( '      * owner private path: ' . $_ownerPrivatePath );

        $this->_privatePath = $_privatePath;
        $this->_ownerPrivatePath = $_ownerPrivatePath;
    }

    /**
     * Delete storage of an instance
     *
     * @param ProvisioningRequest $request
     * @param array               $options
     */
    protected function _removeInstanceStorage( $request, $options = [] )
    {
        $_instance = $request->getInstance();
        $_filesystem = $request->getStorage();
        $_storagePath = $_instance->instance_id_text;

        //  I'm not sure how hard this tries to delete the directory
        $_filesystem->exists( $_storagePath ) && $_filesystem->deleteDirectory( $_storagePath );

        \Log::debug( '    * provisioner: instance storage removed' );
    }

    /** @inheritdoc */
    public function getPrivatePath( $append = null )
    {
        return $this->_privatePath . ( $append ? DIRECTORY_SEPARATOR . ltrim( $append, DIRECTORY_SEPARATOR . ' ' ) : $append );
    }

    /** @inheritdoc */
    public function getOwnerPrivatePath( $append = null )
    {
        //  I hate doing this, but it will make this service more streamlined...
        return ( $this->_hostedStorage ? $this->_ownerPrivatePath : $this->getPrivatePath() ) .
        ( $append ? DIRECTORY_SEPARATOR . ltrim( $append, DIRECTORY_SEPARATOR . ' ' ) : $append );
    }

    /** @inheritdoc */
    public function getOwnerHash( Instance $instance )
    {
        return $instance->user->getHash();
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
     * @return array
     */
    public function getStorageMap()
    {
        return $this->_storageMap;
    }

}