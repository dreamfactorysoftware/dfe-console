<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Describes a service that can create things statically
 */
interface StaticFactory
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new thing
     *
     * @param string $abstract The abstract name of the thing to create
     * @param array  $options  Array of options for creation
     *
     * @return array
     */
    public static function make( $abstract, $options = [] );

}