<?php namespace DreamFactory\Enterprise\Console\Listeners\Events;

use DreamFactory\Enterprise\Console\Listeners\BaseEventHandler;
use DreamFactory\Enterprise\Database\Models\InstanceArchive;

/**
 * Fired when a new instance is deprovisioned
 */
class InstanceUp extends BaseEventHandler
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param \DreamFactory\Enterprise\Database\Models\InstanceArchive $instance
     *
     * @return bool|int
     */
    public function handle(InstanceArchive $instance)
    {
    }
}
