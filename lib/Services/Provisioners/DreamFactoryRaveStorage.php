<?php
namespace DreamFactory\Enterprise\Services\Storage\DreamFactory;

use DreamFactory\Enterprise\Common\Contracts\StorageProvisioner;
use DreamFactory\Enterprise\Common\Filesystems\InstanceFilesystem;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

/**
 * DreamFactory Enterprise(tm) and Services Platform File System
 *
 * The default functionality (static::$partitioned is set to TRUE) of this resolver is to provide partitioned
 * layout paths for the hosted storage area. The structure generated is as follows:
 *
 * /mount_point                             <----- Mount point/absolute path of storage area
 *      /storage                            <----- Root directory of hosted storage
 *          /zone                           <----- The storage zones (ec2.us-east-1a, ec2.us-west-1b, local, etc.)
 *              /[00-ff]                    <----- The first two bytes of hashes within (the partition)
 *                  /user-hash
 *                      /.private           <----- User private storage root
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
 *
 * This class also provides path mapping for non-hosted DSPs as well. Set the $partitioned property to FALSE
 * for this functionality. The structure will use the installation path as a mount point.
 *
 * The structure is as follows:
 *
 * install_root/storage/
 * install_root/storage/applications
 * install_root/storage/plugins
 * install_root/storage/.private
 * install_root/storage/.private/config
 * install_root/storage/.private/.cache
 * install_root/storage/.private/scripts
 * install_root/storage/.private/scripts.user
 */
class DreamFactoryRaveStorage implements StorageProvisioner
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

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /** @inheritdoc */
    public function provision( ProvisioningRequest $request )
    {
        //  Make structure
        $this->_createInstanceStorage( $request->getInstance(), $request->getStorage() );
    }

    /** @inheritdoc */
    public function deprovision( ProvisioningRequest $request )
    {
        //  '86 structure
        $this->_removeInstanceStorage( $request->getInstance(), $request->getStorage() );
    }

    /**
     * Create storage structure in $filesystem
     *
     * @param Instance           $instance
     * @param InstanceFilesystem $filesystem
     */
    protected function _createInstanceStorage( $instance, $filesystem )
    {
        list( $_zone, $_partition, $_rootHash ) = $this->_resolveStructure( $instance, $filesystem->isPartitioned() );

        $_privateName = config( 'dfe.provisioning.private-base-path', '.private' );

        $_rootPath =
            $filesystem->isPartitioned()
                ? DIRECTORY_SEPARATOR . $_zone . DIRECTORY_SEPARATOR . $_partition . DIRECTORY_SEPARATOR . $_rootHash
                : DIRECTORY_SEPARATOR;

        $_userPrivatePath = $_rootPath . DIRECTORY_SEPARATOR . $_privateName;
        $_storageBasePath = $_rootPath . ( $filesystem->isPartitioned() ? DIRECTORY_SEPARATOR . $instance->instance_id_text : null );
        $_privatePath = $_storageBasePath . DIRECTORY_SEPARATOR . $_privateName;

        !$filesystem->exists( $_userPrivatePath ) && $filesystem->makeDirectory( $_userPrivatePath, 0777, true );
        !$filesystem->exists( $_privatePath ) && $filesystem->makeDirectory( $_privatePath, 0777, true );

        foreach ( config( 'dfe.provisioning.public-paths', [] ) as $_path )
        {
            if ( !$filesystem->exists( $_check = $_storageBasePath . DIRECTORY_SEPARATOR . $_path ) )
            {
                $filesystem->makeDirectory( $_check, 0777, true );
            }
        }

        foreach ( config( 'dfe.provisioning.private-paths', [] ) as $_path )
        {
            if ( !$filesystem->exists( $_check = $_privatePath . DIRECTORY_SEPARATOR . $_path ) )
            {
                $filesystem->makeDirectory( $_check, 0777, true );
            }
        }
    }

    /**
     * Delete storage of an instance
     *
     * @param Instance           $instance
     * @param InstanceFilesystem $filesystem
     */
    protected function _removeInstanceStorage( $instance, $filesystem )
    {
        list( $_zone, $_partition, $_rootHash ) = $this->_resolveStructure( $instance, $filesystem->isPartitioned() );
        $_storagePath = $this->_makeRootPath( $filesystem->isPartitioned(), $_zone, $_partition, $_rootHash, $instance->instance_id_text );
        $filesystem->exists( $_storagePath ) && $filesystem->deleteDirectory( $_storagePath );
    }

    /**
     * Based on the requirements, resolve the base components of the storage area
     *
     * @param Instance $instance
     * @param bool     $partitioned
     *
     * @return array
     */
    protected function _resolveStructure( Instance $instance, $partitioned = false )
    {
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

        if ( $partitioned && ( empty( $_zone ) || empty( $_partition ) ) )
        {
            throw new \RuntimeException( 'Zone and/or partition unknown. Cannot provision storage.' );
        }

        return [$_zone, $_partition, $_rootHash];
    }

    /**
     * @param bool   $partitioned
     * @param string $zone
     * @param string $partition
     * @param string $rootHash
     * @param string $instanceId Optional instance id to append to path
     *
     * @return string
     */
    protected function _makeRootPath( $partitioned, $zone, $partition, $rootHash, $instanceId = null )
    {
        return
            $partitioned
                ?
                DIRECTORY_SEPARATOR .
                $zone .
                DIRECTORY_SEPARATOR .
                $partition .
                DIRECTORY_SEPARATOR .
                $rootHash .
                ( $instanceId ? DIRECTORY_SEPARATOR . $instanceId : null )
                : null;
    }
}