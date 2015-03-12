<?php
namespace DreamFactory\Enterprise\Common\Contracts;

use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;

/**
 * Something that looks like it can provision resources
 */
interface ResourceProvisioner
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @param ProvisioningRequest|mixed $request
     *
     * @return mixed
     */
    public function provision( $request );

    /**
     * @param ProvisioningRequest|mixed $request
     *
     * @return mixed
     */
    public function deprovision( $request );
}