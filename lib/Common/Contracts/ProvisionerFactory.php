<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Describes a service that can create provisioners
 */
interface ProvisionerFactory
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new provisioner
     *
     * @param string $provisioner
     * @param array  $options Array of options for creation
     *
     * @return ResourceProvisioner
     */
    public function make( $provisioner, $options = [] );

}