<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Something that is aware of provisioners
 */
interface ResourceProvisionerAware
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @param string $whichOne Which provisioner to retrieve
     *
     * @return ResourceProvisioner
     * @internal param ProvisioningRequest|mixed $request
     *
     */
    public function getProvisioner( $whichOne = null );
}