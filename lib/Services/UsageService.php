<?php namespace DreamFactory\Enterprise\Services;

use Carbon\Carbon;
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
    /**
     * @type string This installation's identifier
     */
    protected $installKey;

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

        empty($this->installKey) && $this->installKey = $this->generateInstallKey();
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
            if (false === $this->installKey) {
                return [];
            }

            return array_merge(['install-key' => $this->installKey,], $this->telemetry->getTelemetry());
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
        if (false === $this->installKey) {
            return [];
        }

        //  Set our installation key
        $_stats = [
            'install-key' => $this->installKey,
        ];

        $_mirror = new \ReflectionClass(get_called_class());

        foreach ($_mirror->getMethods() as $_method) {
            if (preg_match("/^gather(.+)Statistics$/i", $_methodName = $_method->getShortName())) {
                $_which = str_slug(str_ireplace(['gather', 'statistics'], null, $_methodName));
                $_stats[$_which] = call_user_func([get_called_class(), $_methodName], $verbose);
            }
        }

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
            'uri'         => $_uri = config('app.url', \Request::getSchemeAndHttpHost()),
            'install-key' => $this->installKey,
            'user'        => ServiceUser::count(),
            'mount'       => Mount::count(),
            'server'      => Server::count(),
            'cluster'     => Cluster::count(),
            'limit'       => Limit::count(),
            'instance'    => Instance::count(),
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
        $_metrics = null;

        /** @type Instance $_instance */
        foreach (Instance::all() as $_instance) {
            //  See if we even need to do this...
            try {
                $_metrics = $_instance->metrics($_gatherDate);
            } catch (\Exception $_ex) {
                \Log::error('[dfe.usage-service:gatherInstanceStatistics] ' . $_ex->getMessage());
            }

            if (empty($_metrics)) {
                $_api = InstanceApiClient::connect($_instance);

                try {
                    $_list = [];

                    //  Save the environment!!
                    $_stats = [
                        'uri'       => $_instance->getProvisionedEndpoint(),
                        'resources' => [],
                    ];

                    if (false === ($_status = $_api->status()) || empty($_status)) {
                        throw new InstanceNotActivatedException($_instance->instance_id_text);
                    }

                    $_stats['environment'] = array_merge(array_only(array_only($_status, 'platform'), 'version_current'), ['.known-status' => 'activated']);

                    if (false === ($_resources = $_api->resources()) || empty($_resources)) {
                        throw new InstanceNotActivatedException($_instance->instance_id_text);
                    }

                    foreach ($_resources as $_resource) {
                        try {
                            if (false !== ($_result = $_api->resource($_resource))) {
                                $_list[$_resource] = count($_result);
                            } else {
                                $_list[$_resource] = 0;
                            }
                        } catch (\Exception $_ex) {
                            $_list[$_resource] = 'error';
                        }

                        if ($_resource == 'user' && (0 === $_list[$_resource] || 'error' === $_list[$_resource])) {
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
                    $_stats['environment']['platform']['.known-status'] = 'not activated';
                } catch (\Exception $_ex) {
                    \Log::log($verbose ? 'info' : 'debug', '[dfe.usage-service:gatherInstanceStatistics] unknown ' . $_instance->instance_id_text);

                    //  Instance unavailable or not initialized
                    $_stats['environment']['platform']['.known-status'] = 'error';
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

                unset($_metrics, $_stats, $_list, $_status);
            }
        }

        return $this->bundleDailyMetrics($_gatherDate);

        //  The new way
        //return $this->telemetry->make('instance')->getTelemetry();
    }

    /**
     * @param string|Carbon $date
     *
     * @return array [];
     */
    protected function bundleDailyMetrics($date)
    {
        $_gathered = $_totals = [];

        //  Pull all the details up into a single array and return it
        foreach (MetricsDetail::byGatherDate($date)->with('instance')->get() as $_detail) {
            $_metrics = $_detail->data_text;

            //  Aggregate
            foreach (data_get($_metrics, 'resources', []) as $_resource => $_count) {
                !array_key_exists($_resource, $_totals) && $_totals[$_resource] = 0;
                is_numeric($_count) && $_totals[$_resource] += $_count;
            }

            $_gathered[$_detail->instance->instance_id_text] = $_metrics;
        }

        return array_merge(['.aggregate' => $_totals,], $_gathered);
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
