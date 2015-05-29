<?php
namespace DreamFactory\Enterprise\Services\Commands;

class ProvisionJob extends BaseInstanceJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'provision';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string Our handler */
    protected $_handlerClass = 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\ProvisionHandler';
}
