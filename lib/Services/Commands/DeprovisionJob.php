<?php
namespace DreamFactory\Enterprise\Services\Commands;

class DeprovisionJob extends BaseInstanceJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'deprovision';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string Our handler */
    protected $handlerClass = 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\DeprovisionHandler';
}
