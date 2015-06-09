<?php namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * Something that maintains the content and quality of a snapshot
 */
interface SnapshotCustodian
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getMetadata( $key = null, $default = null );

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setMetadata( $key, $value = null );


}