<?php namespace DreamFactory\Enterprise\Console\Handlers\Events;

use DreamFactory\Enterprise\Database\Models\ServiceUser;

/**
 * Fired when a new instance is deprovisioned
 */
class InstanceUp extends BaseEventHandler
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param ServiceUser $user
     * @param bool        $remember
     *
     * @return bool|int
     */
    public function handle( ServiceUser $user, $remember )
    {
    }
}
