<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Describes a service that can import and export snapshots
 */
interface SnapshotArchivist
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Imports snapshot file $snapshot into instance $instanceId
     *
     * @param string $instanceId
     * @param string $snapshot
     *
     * @return mixed
     */
    public function import( $instanceId, $snapshot );

    /**
     * Exports instance $instanceId to snapshot file $snapshot
     *
     * @param string $instanceId
     * @param string $snapshot
     *
     * @return mixed
     */
    public function export( $instanceId, $snapshot );
}