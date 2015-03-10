<?php
namespace DreamFactory\Enterprise\Common\Contracts;

use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;

/**
 * Something that looks like it can provision resources
 */
interface ProvisionerContract
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @param ProvisioningRequest $request
     *
     * @return bool
     */
    public function provision( ProvisioningRequest $request );

    /**
     * @param ProvisioningRequest $request
     *
     * @return bool
     */
    public function deprovision( ProvisioningRequest $request );
}