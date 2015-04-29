<?php namespace DreamFactory\Enterprise\Console\Handlers\Events;

use DreamFactory\Library\Fabric\Database\Models\Deploy\ServiceUser;
use Illuminate\Http\Request;

/**
 * Called when a user logs in
 */
class AuthLoginEventHandler
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Request
     */
    protected $_request;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param Request $request
     */
    function __construct( Request $request )
    {
        $this->_request = $request;
    }

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
