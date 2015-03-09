<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Services\Traits\InstanceValidation;

/**
 * Provides host settings to instances
 */
class HostController extends FactoryController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //********************************************************************************
    //* Methods
    //********************************************************************************

    public function getInstance( $instanceId )
    {

        $_response = [];
    }

}
