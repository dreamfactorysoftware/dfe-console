<?php namespace DreamFactory\Enterprise\Services\Jobs;

use DreamFactory\Enterprise\Common\Jobs\BaseInstanceJob;

class DeprovisionJob extends BaseInstanceJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'deprovision';
}
