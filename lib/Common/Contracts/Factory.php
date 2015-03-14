<?php
namespace DreamFactory\Enterprise\Common\Contracts;

/**
 * Describes a service that can create things
 */
interface Factory
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
    public function make( $abstract, $options = [] );

}