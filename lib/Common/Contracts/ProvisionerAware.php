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
     * @return ResourceProvisioner
     */
    public function getProvisioner();
}