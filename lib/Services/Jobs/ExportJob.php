<?php namespace DreamFactory\Enterprise\Services\Jobs;

use DreamFactory\Enterprise\Common\Jobs\PortabilityJob;

class ExportJob extends PortabilityJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'export';
}