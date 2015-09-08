<?php namespace DreamFactory\Enterprise\Console\Listeners\Events;

use DreamFactory\Enterprise\Console\Listeners\BaseEventHandler;
use DreamFactory\Enterprise\Database\Models\Instance;

/**
 * Fired when a new instance is provisioned
 */
class InstanceUp extends BaseEventHandler
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return bool|int
     */
    public function handle(Instance $instance)
    {
    }
}
