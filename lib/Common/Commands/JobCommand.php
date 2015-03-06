<?php
namespace DreamFactory\Enterprise\Common\Commands;

use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * A base class for all "job" type commands (non-console)
 */
abstract class JobCommand implements ShouldBeQueued
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string|bool The queue upon which to push myself. Set to false to not use queuing
     */
    const JOB_QUEUE = false;

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InteractsWithQueue, SerializesModels;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string The handler class for this job if different from "[class-name]Handler"
     */
    abstract public function getHandler();

}
