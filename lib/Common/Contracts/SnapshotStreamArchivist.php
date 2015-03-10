<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Describes a service that can import and export snapshot streams
 */
interface SnapshotStreamArchivist
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Imports snapshot file $snapshot into instance $instanceId
     *
     * @param string   $instanceId
     * @param resource $snapshotStream
     *
     * @return mixed
     */
    public function importStream( $instanceId, $snapshotStream );

    /**
     * Exports instance $instanceId to snapshot file $snapshot
     *
     * @param string   $instanceId
     * @param resource $snapshotStream
     *
     * @return mixed
     */
    public function exportStream( $instanceId, $snapshotStream );
}