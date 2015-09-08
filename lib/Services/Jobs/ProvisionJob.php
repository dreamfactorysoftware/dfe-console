<?php namespace DreamFactory\Enterprise\Services\Jobs;

use DreamFactory\Enterprise\Common\Jobs\BaseInstanceJob;

class ProvisionJob extends BaseInstanceJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'provision';
}
