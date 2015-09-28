<?php namespace DreamFactory\Enterprise\Console\Listeners\Events;

use DreamFactory\Enterprise\Console\Listeners\BaseEventHandler;
use DreamFactory\Enterprise\Database\Models\ServiceUser;

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
    public function handle(ServiceUser $user, $remember)
    {
        logger('[auth.login] ' . $user->email_addr_text . ' authorization');

        return $user->update([
            'last_login_date'    => $user->freshTimestamp(),
            'last_login_ip_text' => $this->request->getClientIp(),
        ]);
    }
}
