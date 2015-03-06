<?php
namespace DreamFactory\Enterprise\Services\Contracts\Instance;

/**
 * Describes a service that can create instance rows
 */
interface Factory
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