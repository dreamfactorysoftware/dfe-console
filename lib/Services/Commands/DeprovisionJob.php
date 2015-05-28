<?php
namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\EnterpriseJobCommand;

class DeprovisionJob extends EnterpriseJobCommand
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'deprovision';
}
