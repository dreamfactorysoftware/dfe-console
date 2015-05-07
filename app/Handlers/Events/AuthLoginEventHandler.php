<?php namespace DreamFactory\Enterprise\Console\Handlers\Events;

use DreamFactory\Library\Fabric\Database\Models\Deploy\ServiceUser;

/**
 * Called when a user logs in
 */
class AuthLoginEventHandler extends BaseEventHandler
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
        return $user->update(
            [
                'last_login_date'    => $user->freshTimestamp(),
                'last_login_ip_text' => $this->_request->getClientIp()
            ]
        );
    }
}