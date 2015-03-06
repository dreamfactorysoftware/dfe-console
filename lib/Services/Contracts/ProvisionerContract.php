<?php
namespace DreamFactory\Enterprise\Services\Contracts;

use DreamFactory\Enterprise\Services\Requests\ProvisioningRequest;

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