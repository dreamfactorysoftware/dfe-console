<?php
//******************************************************************************
//* DFE Helper Functions
//******************************************************************************

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;

if ( !function_exists( 'ioc_name' ) )
{
    /**
     * @param BaseServiceProvider $provider
     *
     * @return string Returns IoC name of given provider or NULL if the service does not have one
     */
    function ioc_name( $provider )
    {
        return is_callable( $provider ) ? $provider() : null;
    }
}