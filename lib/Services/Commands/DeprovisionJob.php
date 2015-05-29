<?php
namespace DreamFactory\Enterprise\Services\Commands;

class DeprovisionJob extends BaseEnterpriseJob
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
    protected $_handlerClass = 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\DeprovisionHandler';
}
