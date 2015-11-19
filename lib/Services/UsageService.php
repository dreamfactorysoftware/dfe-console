<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Enterprise\Database\Models\Mount;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Instance\Ops\Facades\InstanceApiClient;
use DreamFactory\Enterprise\Services\Contracts\MetricsProvider;
use DreamFactory\Enterprise\Services\Facades\Telemetry;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * General usage services
 */
class UsageService extends BaseService implements MetricsProvider
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type TelemetryService
     */
    protected $telemetry;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /** @inheritdoc */
    public function boot()
    {
        parent::boot();

        //  Get an instance of the telemetry service and register any configured providers
        $this->telemetry = Telemetry::service();

        //  Register the configured providers
        foreach (config('dfe.audit.telemetry-providers', []) as $_name => $_provider) {
            $this->telemetry->registerProvider($_name, $_provider);
        }
    }

    /**
     * Retrieves the metrics
     *
     * @param array|null $options Any options around providing the data
     *
     * @return mixed|array
     */
    public function getMetrics($options = [])
    {
        //  If nobody has used the system, we can't report metrics
        if (false === ($_installKey = $this->generateInstallKey())) {
            return [];
        }

        return array_merge(['install-key' => $_installKey,], $this->telemetry->getTelemetry());
    }

    /**
     * Returns statistics gathered from various sources as defined by the methods in this class and its subclasses
     *
     * @return array
     */
    public function gatherStatistics()
    {
        $_stats = [];

        if (false === ($_installKey = $this->generateInstallKey())) {
            return [];
        }

        $_mirror = new \ReflectionClass(get_called_class());

        foreach ($_mirror->getMethods() as $_method) {
            if (preg_match("/^gather(.+)Statistics$/i", $_methodName = $_method->getShortName())) {
                $_which = str_slug(str_ireplace(['gather', 'statistics'], null, $_methodName));
                $_stats[$_which] = call_user_func([get_called_class(), $_methodName]);
            }
        }

        //  Set our installation key
        $_stats['install-key'] = $_installKey;

        return $_stats;
    }

    /**
     * @return bool|string
     */
    public function generateInstallKey()
    {
        try {
            /** @type ServiceUser $_user */
            $_user = ServiceUser::firstOrFail();

            return $_user->getHashedEmail();
        } catch (ModelNotFoundException $_ex) {
            \Log::notice('No console users found. Nothing to report.');

            return false;
        }
    }

    /**
     * @return array
     */
    protected function gatherConsoleStatistics()
    {
        $_stats = [
            'uri'      => config('app.url', \Request::getSchemeAndHttpHost()),
            'user'     => ServiceUser::count(),
            'mount'    => Mount::count(),
            'server'   => Server::count(),
            'cluster'  => Cluster::count(),
            'limit'    => Limit::count(),
            'instance' => Instance::count(),
        ];

        return $_stats;

        //  The new way
        //return $this->telemetry->make('console')->getTelemetry();
    }

    /**
     * @return array
     */
    protected function gatherDashboardStatistics()
    {
        $_stats = [
            'user' => User::count(),
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
            $_stats[$_instance->instance_id_text] = ['uri' => $_instance->getProvisionedEndpoint(),];

            $_api = InstanceApiClient::connect($_instance);

            try {
                if (!empty($_resources = $_api->resources())) {
                    $_list = [];

                    foreach ($_resources as $_resource) {
                        if (property_exists($_resource, 'name')) {
                            try {
                                if (false !== ($_result = $_api->resource($_resource->name))) {
                                    $_list[$_resource->name] = count($_result);
                                }
                            } catch (\Exception $_ex) {
                                $_list[$_resource->name] = 'unavailable';
                            }
                        }
                    }

                    $_stats[$_instance->instance_id_text]['resources'] = $_list;
                    $_stats[$_instance->instance_id_text]['_status'] = ['operational'];
                }
            } catch (\Exception $_ex) {
                //  Instance unavailable or not initialized
                $_stats[$_instance->instance_id_text]['resources'] = [];
                $_stats[$_instance->instance_id_text]['_status'] = ['unreachable'];
            }
        }

        return $_stats;
    }
}
