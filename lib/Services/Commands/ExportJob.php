<?php
namespace DreamFactory\Enterprise\Services\Commands;

class ExportJob extends BaseInstanceJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'export';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string Our handler */
    protected $_handlerClass = 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\ExportHandler';
}
