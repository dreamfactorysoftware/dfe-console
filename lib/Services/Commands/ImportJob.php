<?php
namespace DreamFactory\Enterprise\Services\Commands;

class ImportJob extends BaseInstanceJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'import';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string Our handler */
    protected $_handlerClass = 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\ImportHandler';
}
