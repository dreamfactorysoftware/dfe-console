<?php
namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * A contract for offering provisioners
 */
interface OfferingProvisioner
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @return Offering[]
     */
    public function getOfferings();

    /**
     * @return string The id of this provisioner
     */
    public function getProvisionerId();
}