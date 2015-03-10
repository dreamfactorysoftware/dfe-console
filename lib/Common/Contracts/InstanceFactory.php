<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Describes a service that can create instance rows
 */
interface InstanceFactory
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new instance record
     *
     * @param string $instanceName
     * @param array  $options Array of options for creation
     *
     * @return array
     */
    public function make( $instanceName, $options = [] );

}