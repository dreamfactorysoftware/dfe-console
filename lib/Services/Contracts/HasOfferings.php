<?php namespace DreamFactory\Enterprise\Common\Services\Provisioners\Offerings;

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