<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Cluster;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Fabric\Database\Models\Deploy\ServiceUser;
use Illuminate\Support\Facades\Auth;

class FactoryController extends Controller
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type int
     */
    const DEFAULT_PER_PAGE = 25;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type bool True if this is a datatables request
     */
    protected $_dtRequest = false;
    /**
     * @type int
     */
    protected $_skip = null;
    /**
     * @type int
     */
    protected $_limit = null;
    /**
     * @type array
     */
    protected $_order = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param bool $asArray
     *
     * @return array|string The hashed email address
     */
    public static function getUserHash( $asArray = false )
    {
        $_hash = md5( strtolower( Auth::user() ? Auth::user()->email : 'nobody@dreamfactory.com' ) );

        return $asArray ? ['_userHash' => $_hash] : $_hash;
    }

    /**
     * @return array
     */
    public static function getUserInfo()
    {
        $_name = \Auth::user() ? Auth::user()->email : 'nobody@dreamfactory.com';
        $_hash = md5( strtolower( $_name ) );

        return [
            'name' => $_name,
            'hash' => $_hash,
        ];
    }

    /**
     * Get and cache array of database stats
     *
     * @return array
     */
    public static function getActiveCounts()
    {
        $_counts = \Cache::get( 'console.active_counts' );

        if ( empty( $_counts ) )
        {
            $_counts = [
                'clusters'  => Cluster::count(),
                'users'     => ServiceUser::count(),
                'instances' => Instance::count(),
                'servers'   => Server::count(),
            ];

            \Cache::put( 'console.active_counts', $_counts, 1 );
        }

        return $_counts;
    }
}
