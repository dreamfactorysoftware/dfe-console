<?php namespace DreamFactory\Enterprise\Services\Contracts;

use DreamFactory\Enterprise\Services\Utility\InstanceMetadata;

/**
 * Something that maintains the content and quality of a snapshot
 */
interface SnapshotCustodian
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return InstanceMetadata
     */
    public function getMetadata();

    /**
     * @param \DreamFactory\Enterprise\Services\Utility\InstanceMetadata $metadata
     *
     * @return $this
     * @internal param array $array The metadata array
     *
     */
    public function setMetadata(InstanceMetadata $metadata);

}