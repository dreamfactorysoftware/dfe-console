<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Database\Exceptions\InstanceNotActivatedException;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Enterprise\Database\Models\MetricsDetail;
use DreamFactory\Enterprise\Database\Models\Mount;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Instance\Ops\Facades\InstanceApiClient;
use DreamFactory\Enterprise\Services\Contracts\MetricsProvider;
use DreamFactory\Enterprise\Services\Facades\Telemetry;
use DreamFactory\Library\Utility\Curl;
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

        //  If we're using telemetry, get an instance of the telemetry service and register any providers
        if (config('telemetry.enabled', false)) {
            $this->telemetry = Telemetry::service();

            //  Register the configured providers
            foreach (config('telemetry.providers', []) as $_name => $_provider) {
                $this->telemetry->registerProvider($_name, new $_provider());
            }
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
        if (config('telemetry.enabled', false)) {
            //  If nobody has used the system, we can't report metrics
            if (false === ($_installKey = $this->generateInstallKey())) {
                return [];
            }

            return array_merge(['install-key' => $_installKey,], $this->telemetry->getTelemetry());
        }

        return $this->gatherStatistics();
    }

    /**
     * Returns statistics gathered from various sources as defined by the methods in this class and its subclasses
     *
     * @param bool $send If true, send metrics data to usage endpoint
     * @param bool $verbose
     *
     * @return array
     */
    public function gatherStatistics($send = false, $verbose = false)
    {
        $_stats = [];

        if (false === ($_installKey = $this->generateInstallKey())) {
            return [];
        }

        $_mirror = new \ReflectionClass(get_called_class());

        foreach ($_mirror->getMethods() as $_method) {
            if (preg_match("/^gather(.+)Statistics$/i", $_methodName = $_method->getShortName())) {
                $_which = str_slug(str_ireplace(['gather', 'statistics'], null, $_methodName));
                $_stats[$_which] = call_user_func([get_called_class(), $_methodName], $verbose);
            }
        }

        //  Set our installation key
        $_stats['install-key'] = $_installKey;

        //  Send metrics if wanted
        $send && $this->sendMetrics($_stats, $verbose);

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
     * @param bool $verbose
     *
     * @return array
     */
    protected function gatherConsoleStatistics($verbose = false)
    {
        $_stats = [
            'uri'      => $_uri = config('app.url', \Request::getSchemeAndHttpHost()),
            'user'     => ServiceUser::count(),
            'mount'    => Mount::count(),
            'server'   => Server::count(),
            'cluster'  => Cluster::count(),
            'limit'    => Limit::count(),
            'instance' => Instance::count(),
        ];

        \Log::log($verbose ? 'info' : 'debug', '[dfe.usage-service:gatherConsoleStatistics] ** ' . $_uri);

        return $_stats;

        //  The new way
        //return $this->telemetry->make('console')->getTelemetry();
    }

    /**
     * @param bool $verbose
     *
     * @return array
     */
    protected function gatherDashboardStatistics($verbose = false)
    {
        $_stats = [
            'user' => User::count(),
        ];

        \Log::log($verbose ? 'info' : 'debug', '[dfe.usage-service:gatherDashboardStatistics] ** ' . json_encode($_stats));

        return $_stats;

        //  The new way
        //return $this->telemetry->make('dashboard')->getTelemetry();
    }

    /**
     * @param bool $verbose
     *
     * @return array
     */
    protected function gatherInstanceStatistics($verbose = false)
    {
        $_gatherDate = date('Y-m-d');
        $_gathered = 0;

        /** @type Instance $_instance */
        foreach (Instance::all() as $_instance) {
            $_stats = ['uri' => $_instance->getProvisionedEndpoint(),];

            $_api = InstanceApiClient::connect($_instance);

            try {
                if (false === ($_status = $_api->status()) || empty($_status)) {
                    throw new InstanceNotActivatedException($_instance->instance_id_text);
                }

                if (false === ($_resources = $_api->resources()) || empty($_resources)) {
                    throw new InstanceNotActivatedException($_instance->instance_id_text);
                }

                //  Save the environment!!
                $_stats['environment'] = $_status;
                $_stats['resources'] = [];
                $_stats['_status'] = ['activated'];

                $_list = [];

                foreach ($_resources as $_resource) {
                    try {
                        if (false !== ($_result = $_api->resource($_resource))) {
                            $_list[$_resource] = count($_result);
                        } else {
                            $_list[$_resource] = 'unknown';
                        }
                    } catch (\Exception $_ex) {
                        $_list[$_resource] = 'unknown';
                    }

                    if ($_resource == 'user' && $_list[$_resource] == 'unknown') {
                        //  database is setup but no users...
                        throw new InstanceNotActivatedException($_instance->instance_id_text);
                    }
                }

                \Log::log($verbose ? 'info' : 'debug', '[dfe.usage-service:gatherInstanceStatistics] active ' . $_instance->instance_id_text);
                $_stats['resources'] = $_list;
            } catch (InstanceNotActivatedException $_ex) {
                \Log::log($verbose ? 'info' : 'debug',
                    '[dfe.usage-service:gatherInstanceStatistics] inactive ' . $_ex->getInstanceId());

                //  Instance unavailable or not initialized
                $_stats['_status'] = ['not activated'];
            } catch (\Exception $_ex) {
                \Log::log($verbose ? 'info' : 'debug', '[dfe.usage-service:gatherInstanceStatistics] unknown ' . $_instance->instance_id_text);

                //  Instance unavailable or not initialized
                $_stats['_status'] = ['unknown'];
            }

            try {
                if (null === ($_row = $_instance->metrics($_gatherDate))) {
                    $_row = new MetricsDetail();
                    $_row->user_id = $_instance->user_id;
                    $_row->instance_id = $_instance->id;
                    $_row->gather_date = $_gatherDate;
                }

                $_row->data_text = $_stats;
                $_row->save();

                $_gathered++;
            } catch (\Exception $_ex) {
                \Log::error('[dfe.usage-service:gatherInstanceStatistics] ' . $_ex->getMessage());
            }

            unset($_stats);
        }

        $_gathered = [];

        //  Pull all the details up into a single array and return it
        foreach (MetricsDetail::byGatherDate($_gatherDate)->with('instance')->get() as $_detail) {
            $_gathered[$_detail->instance->instance_id_text] = $_detail->data_text;
        }

        return $_gathered;

        //  The new way
        //return $this->telemetry->make('instance')->getTelemetry();
    }

    /**
     * @param array $stats
     * @param bool  $verbose
     *
     * @return bool
     */
    public function sendMetrics(array $stats, $verbose = false)
    {
        if (null !== ($_endpoint = config('license.endpoints.usage'))) {
            try {
                if (false === ($_result = Curl::post($_endpoint, json_encode($stats), [CURLOPT_HTTPHEADER => ['Content-Type: application/json']]))) {
                    throw new \RuntimeException('Network error during metrics send..');
                }

                \Log::log($verbose ? 'info' : 'debug', '[dfe.usage-service:sendMetrics] usage data sent to ' . $_endpoint, $stats);

                return true;
            } catch (\Exception $_ex) {
                \Log::error('[dfe.usage-service:sendMetrics] exception reporting usage data: ' . $_ex->getMessage());
            }
        } else {
            \Log::notice('[dfe.usage-service:sendMetrics] No usage endpoint found for metrics send.');
        }

        return false;
    }
}
