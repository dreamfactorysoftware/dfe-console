<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
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
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Returns statistics gathered from various sources as defined by the methods in this class and its subclasses
     *
     * @return array
     */
    public function gatherStatistics()
    {
        //  Start out with our installation key
        $_stats = [
            'install-key' => config('dfe.install-key'),
        ];

        $_mirror = new \ReflectionClass(get_called_class());

        foreach ($_mirror->getMethods() as $_method) {
            if (preg_match("/^gather(.+)Statistics$/i", $_methodName = $_method->getShortName())) {
                $_which = str_slug(str_ireplace(['gather', 'statistics'], null, $_methodName));
                $_stats[$_which] = call_user_func([get_called_class(), $_methodName]);
            }
        }

        return $_stats;
    }

    /**
     * @return array
     */
    protected function gatherConsoleStatistics()
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
    protected function gatherDashboardStatistics()
    {
        $_stats = [
            'users' => User::count(),
        ];

        return $_stats;
    }

    /**
     * @return array
     */
    protected function gatherInstanceStatistics()
    {
        $_stats = [];
        $_lastGuestLocation = null;

        /** @type Instance $_instance */
        foreach (Instance::all() as $_instance) {
            $_resourceUri =
                config('provisioners.hosts.' .
                    GuestLocations::resolve($_instance->guest_location_nbr) .
                    '.resource-uri');

            if (!empty($_resourceUri)) {
                $_resources = $_instance->call($_resourceUri);

                foreach ($_resources as $_resource) {
                    try {
                        if (false !== ($_result = $_instance->getResource($_resource))) {
                            $_stats[$_resource] = count($_result->response);
                        }
                    } catch (\Exception $_ex) {
                        $_stats[$_resource] = 'unavailable';
                    }
                }
            }
        }

        return $_stats;
    }
}