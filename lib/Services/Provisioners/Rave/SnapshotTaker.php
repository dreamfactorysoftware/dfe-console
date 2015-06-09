<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Services\Contracts\SnapshotCustodian;
use DreamFactory\Enterprise\Services\Contracts\TakesSnapshots;
use DreamFactory\Enterprise\Services\Facades\Snapshot;
use League\Flysystem\Filesystem;

/**
 * DSP snapshot provisioner
 */
class SnapshotTaker implements TakesSnapshots
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param int|string $instanceId  The instance id
     * @param Filesystem $destination The optional destination to store the created snapshot. The default is to store it in the
     * @param int        $expires
     *
     * @return SnapshotCustodian
     */
    public function createSnapshot( $instanceId, Filesystem $destination = null, $expires = null )
    {
        return Snapshot::create( $instanceId, $destination, $expires );
    }

    /**
     * @param string $snapshotId The id of the snapshot
     *
     * @return string
     */
    public function getSnapshot( $snapshotId )
    {
        return Snapshot::get( $snapshotId );
    }

    /**
     * Delete a snapshot
     *
     * @param string $snapshotId The id of the snapshot
     * @param bool   $softDelete If true, file will be moved to "trash" and not deleted. This overrides the system setting
     *
     * @return bool
     */
    public function deleteSnapshot( $snapshotId, $softDelete = null )
    {
        return Snapshot::delete( $snapshotId, $softDelete );
    }

    /**
     * Checks if a snapshot has reached it's expiration date.
     *
     * @param string $snapshotId The id of the snapshot
     *
     * @return bool
     */
    public function isSnapshotExpired( $snapshotId )
    {
        return Snapshot::isExpired( $snapshotId );
    }
}