<?php namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Contracts\HostedStorageAware;
use DreamFactory\Enterprise\Common\Contracts\PrivatePathAware;
use DreamFactory\Enterprise\Common\Contracts\VirtualProvisioner;
use DreamFactory\Enterprise\Common\Exceptions\NotImplementedException;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceRequest;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceResponse;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\HasPrivatePaths;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Traits\InstanceValidation;

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
abstract class BaseStorageProvisioner extends BaseService implements HostedStorageAware, PrivatePathAware, VirtualProvisioner
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string Your provisioner id
     */
    const PROVISIONER_ID = false;

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation, Archivist, HasPrivatePaths;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type bool Indicates if the storage I govern is hosted or standalone
     */
    protected $hosted = true;
    /**
     * @type array The map of storage segments
     */
    protected $storageMap = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param ProvisionServiceRequest $request
     *
     * @return ProvisionServiceResponse
     */
    abstract protected function doProvision($request);

    /**
     * @param ProvisionServiceRequest $request
     *
     * @return ProvisionServiceResponse
     */
    abstract protected function doDeprovision($request);

    /** @inheritdoc */
    public function provision($request)
    {
        return $this->doProvision($request);
    }

    /** @inheritdoc */
    public function deprovision($request)
    {
        return $this->doDeprovision($request);
    }

    /** @inheritdoc */
    public function getProvisionerId()
    {
        if (!static::PROVISIONER_ID) {
            throw new NotImplementedException('No provisioner id has been set.');
        }

        return static::PROVISIONER_ID;
    }

    /**
     * @return array
     */
    public function getStorageMap()
    {
        return $this->storageMap;
    }

    /** @inheritdoc */
    public function getOwnerHash(Instance $instance)
    {
        return $instance->user->getHash();
    }

    /**
     * @return boolean
     */
    public function isHosted()
    {
        return $this->hosted;
    }

    /**
     * @param boolean $hosted
     *
     * @return $this
     */
    public function seHosted($hosted)
    {
        $this->hosted = !!$hosted;

        return $this;
    }

}