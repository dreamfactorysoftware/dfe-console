<?php
namespace DreamFactory\Enterprise\Common\Contracts;

use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;

/**
 * Describes a service that controls hosted instances
 */
interface InstanceProvisioner
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates an instance
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return array
     */
    public function up( ProvisioningRequest $request );

    /**
     * Destroys an instance
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function down( ProvisioningRequest $request );

    /**
     * Replaces an instance
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function replace( ProvisioningRequest $request );

    /**
     * Performs a complete wipe of an instance. The instance is not destroyed, but the database is completely wiped and recreated as if this were a
     * brand new instance. Files in the storage area are NOT touched.
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function wipe( ProvisioningRequest $request );
}