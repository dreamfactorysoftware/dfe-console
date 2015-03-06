<?php
namespace DreamFactory\Enterprise\Services\Contracts\Instance;

/**
 * Describes a service that manages hosted instances
 */
interface Manager
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a snapshot of a fabric-hosted instance
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function launch( $ownerId, $instanceId );

    /**
     * Destroys a DSP
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function destroy( $ownerId, $instanceId );

    /**
     * Replaces a DSP with an existing snapshot
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     * @param string     $snapshot   The path to the snapshot file
     *
     * @return mixed
     */
    public function replace( $ownerId, $instanceId, $snapshot );

    /**
     * Stops a DSP if supported by host
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function stop( $ownerId, $instanceId );

    /**
     * Starts a DSP if supported by host
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function start( $ownerId, $instanceId );

    /**
     * Restart/reboot a DSP if supported by host
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function restart( $ownerId, $instanceId );

    /**
     * Performs a complete wipe of a DSP. The DSP is not destroyed, but the database is completely wiped and recreated as if this were a brand new
     * DSP. Files in the storage area are NOT touched.
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function wipe( $ownerId, $instanceId );
}