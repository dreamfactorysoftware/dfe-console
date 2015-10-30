<?php namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Contracts\VirtualProvisioner;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceRequest;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceResponse;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\HasPrivatePaths;
use DreamFactory\Enterprise\Database\Traits\InstanceValidation;

us  DreamFactory\Enterprise\Common\Exceptions\NotImplementedException;

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
}
