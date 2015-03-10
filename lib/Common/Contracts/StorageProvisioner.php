<?php
namespace DreamFactory\Enterprise\Common\Contracts;

use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;

/**
 * Describes a service that provisions storage
 */
interface StorageProvisioner
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a storage file system
     *
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     *
     * @return bool
     */
    public function provision( ProvisioningRequest $request );

    /**
     * Destroys a provisioned file system
     *
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     *
     * @return bool
     */
    public function deprovision( ProvisioningRequest $request );

}