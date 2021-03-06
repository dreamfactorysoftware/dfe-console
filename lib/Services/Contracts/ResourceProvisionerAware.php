<?php namespace DreamFactory\Enterprise\Services\Contracts;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;
use DreamFactory\Enterprise\Services\Provisioners\ProvisionServiceResponse;

/**
 * Something that is aware of provisioners
 */
interface ResourceProvisionerAware
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Returns an instance of the provisioner $name
     *
     * @param string $name
     *
     * @return ResourceProvisioner
     */
    public function getProvisioner($name = null);

    /**
     * Returns an instance of the storage provisioner $name
     *
     * @param string $name
     *
     * @return ResourceProvisioner
     */
    public function getStorageProvisioner($name = null);

    /**
     * Returns an instance of the db provisioner $name
     *
     * @param string $name
     *
     * @return ResourceProvisioner
     */
    public function getDatabaseProvisioner($name = null);

    /**
     * Provision an instance
     *
     * @param \DreamFactory\Enterprise\Services\Jobs\ProvisionJob $job
     *
     * @return ProvisionServiceResponse|mixed
     */
    public function provision(ProvisionJob $job);

    /**
     * Deprovision an instance
     *
     * @param \DreamFactory\Enterprise\Services\Jobs\DeprovisionJob $job
     *
     * @return ProvisionServiceResponse|mixed
     */
    public function deprovision(DeprovisionJob $job);

}
