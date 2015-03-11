<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Describes a service that can create instance rows statically
 */
interface StaticInstanceFactory
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
    public static function make( $instanceName, $options = [] );

}