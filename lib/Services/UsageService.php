<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Enterprise\Database\Models\Mount;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\User;

/**
 * General usage services
 */
class UsageService extends BaseService
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use Archivist, EntityLookup, Notifier;

    //******************************************************************************
    //* Members
    //******************************************************************************

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Returns statistics gathered from various sources as defined by the methods in this class and its subclasses
     *
     * @param mixed $start A start time/date
     * @param mixed $end   An end time/date
     *
     * @return array
     */
    protected static function gatherStatistics($start = null, $end = null)
    {
        //  Start out with our installation key
        $_stats = [
            'install-key' => config('dfe.install-key'),
        ];

        $_mirror = new \ReflectionClass(get_called_class());

        foreach ($_mirror->getMethods() as $_method) {
            if (preg_match("/^gather(.+)Statistics$/i", $_methodName = $_method->getShortName())) {
                $_which = str_slug(str_ireplace(['gather', 'statistics'], null, $_methodName));
                $_stats[$_which] = call_user_func_array(['static::' . $_methodName], [$start, $end]);
            }
        }

        return $_stats;
    }

    /**
     * @return array
     */
    protected static function gatherConsoleStatistics()
    {
        $_stats = [
            'users'     => ServiceUser::count(),
            'mounts'    => Mount::count(),
            'servers'   => Server::count(),
            'clusters'  => Cluster::count(),
            'limits'    => Limit::count(),
            'instances' => Instance::count(),
        ];

        return $_stats;
    }

    /**
     * @return array
     */
    protected static function gatherDashboardStatistics()
    {
        $_stats = [
            'users' => User::count(),
        ];

        return $_stats;
    }

    /**
     * @return array
     */
    protected static function gatherInstanceStatistics()
    {
        /** @type Instance $_instance */
        foreach (Instance::all() as $_instance) {
        }
    }
}