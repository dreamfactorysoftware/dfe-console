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
    //* Methods
    //******************************************************************************

    /**
     * @return string The handler class for this job if different from "[class-name]Handler"
     */
    public function getHandler()
    {
        return 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\ExportHandler';
    }
}
