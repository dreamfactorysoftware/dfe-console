<?php
namespace DreamFactory\Enterprise\Services\Contracts\Instance;

use DreamFactory\Enterprise\Services\Requests\ProvisioningRequest;

/**
 * Describes a service that controls hosted instances
 */
interface Control
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a fabric-hosted instance
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return array
     */
    public function launch( ProvisioningRequest $request );

    /**
     * Destroys a DSP
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function destroy( ProvisioningRequest $request );

    /**
     * Replaces a DSP with an existing snapshot
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function replace( ProvisioningRequest $request );

    /**
     * Performs a complete wipe of a DSP. The DSP is not destroyed, but the database is completely wiped and recreated as if this were a brand new
     * DSP. Files in the storage area are NOT touched.
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function wipe( ProvisioningRequest $request );

}