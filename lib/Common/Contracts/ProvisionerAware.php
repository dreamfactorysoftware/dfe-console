<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Describes a thing that has a provisioner
 */
interface ProvisionerAware
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Returns an instance of the provisioner $name
     *
     * @param string $name
     *
     * @return ResourceProvisioner
     */
    public function getProvisioner( $name = null );

    /**
     * Returns an instance of the storage provisioner $name
     *
     * @param string $name
     *
     * @return ResourceProvisioner
     */
    public function getStorageProvisioner( $name = null );
}