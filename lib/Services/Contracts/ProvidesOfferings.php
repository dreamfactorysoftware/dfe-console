<?php namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * The contract for a provisioner who provides offerings
 */
interface ProvidesOfferings
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Returns the offerings available for this provisioner
     *
     * @return Offering[]
     */
    public function getOfferings();
}