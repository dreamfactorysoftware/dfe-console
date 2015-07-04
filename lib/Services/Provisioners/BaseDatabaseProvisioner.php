<?php namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Contracts\VirtualProvisioner;
use DreamFactory\Enterprise\Common\Exceptions\NotImplementedException;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceRequest;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceResponse;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\HasPrivatePaths;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Traits\InstanceValidation;

abstract class BaseDatabaseProvisioner extends BaseService implements VirtualProvisioner
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
        return $instance->user && $instance->user->getHash();
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
