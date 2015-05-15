<?php namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * The contract for a provisioner who has offerings
 */
interface HasOfferings
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return Offering[]
     */
    public function getOfferings();
}