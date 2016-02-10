<?php namespace DreamFactory\Enterprise\Services;

use Carbon\Carbon;
use DreamFactory\Enterprise\Common\Enums\InstanceStates;
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
use DreamFactory\Enterprise\Instance\Ops\Services\InstanceApiClientService;
use DreamFactory\Enterprise\Services\Contracts\MetricsProvider;
use DreamFactory\Enterprise\Services\Facades\Telemetry;
use DreamFactory\Library\Utility\Curl;
use DreamFactory\Library\Utility\Json;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * General usage services
 */
class UsageService extends BaseService implements MetricsProvider
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The metrics format version
     */
    const METRICS_VERSION = '1.0';

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
    public function __construct($app)
    {
        parent::__construct($app);

        $this->installKey = $this->generateInstallKey();
    }

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
        //  If nobody has used the system, we can't report metrics
        if (false === $this->installKey) {
            return [];
        }

        $_metrics = config('telemetry.enabled', false) ? $this->telemetry->getTelemetry() : $this->gatherStatistics();

        return $this->bundleMetrics($_metrics);
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
        //  Set our installation key
        $_stats = [];

        $_mirror = new \ReflectionClass(get_called_class());

        foreach ($_mirror->getMethods() as $_method) {
            if (preg_match("/^gather(.+)Statistics$/i", $_methodName = $_method->getShortName())) {
                $_which = str_slug(str_ireplace(['gather', 'statistics'], null, $_methodName));
                $_stats[$_which] = call_user_func([get_called_class(), $_methodName], $verbose);
            }
        }

        //  Move aggregate to console
        if (!empty($_aggregate = data_get($_stats, 'instance._aggregated'))) {
            $_stats = array_set($_stats, 'console.aggregate', $_aggregate);
            array_forget($_stats, 'instance._aggregated');
        }

        return $this->bundleMetrics($_stats, $send);
    }

    /**
     * @return bool|string
     */
    public function generateInstallKey()
    {
        if (empty($_key = config('metrics.install-key'))) {
            try {
                /** @type ServiceUser $_user */
                $_user = ServiceUser::firstOrFail();

                $_key = sha1(config('provisioning.default-domain') . $_user->getHashedEmail());

                file_put_contents(config_path('metrics.php'),
                    '<?php' .
                    PHP_EOL .
                    '// This file was automatically generated on ' .
                    date('c') .
                    ' by dfe.usage-service' .
                    PHP_EOL .
                    "return ['install-key' => '" .
                    $_key .
                    "',];");
            } catch (ModelNotFoundException $_ex) {
                \Log::notice('No console users found. Nothing to report.');

                return false;
            }

            config(['metrics.install-key' => $_key]);
        }

        return $_key;
    }

    /**
     * @param bool $verbose
     *
     * @return array
     */
    protected function gatherConsoleStatistics($verbose = false)
    {
        $_stats = [
            'uri'       => $_uri = config('app.url', \Request::getSchemeAndHttpHost()),
            'resources' => [
                'user'     => ServiceUser::count(),
                'mount'    => Mount::count(),
                'server'   => Server::count(),
                'cluster'  => Cluster::count(),
                'limit'    => Limit::count(),
                'instance' => Instance::count(),
            ],
        ];

        \Log::debug('[dfe.usage-service:gatherConsoleStatistics] ** ' . $_uri);

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
            'uri'       => $_uri = config('dfe.dashboard-url'),
            'resources' => [
                'user' => User::count(),
            ],
        ];

        \Log::debug('[dfe.usage-service:gatherDashboardStatistics] ** ' . $_uri);

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
        $_gathered = 0;
        $_gatherDate = date('Y-m-d');
        $_metrics = null;

        /** @type Instance $_instance */
        foreach (Instance::all() as $_instance) {
            $_api = InstanceApiClient::connect($_instance);

            try {
                //  Save the environment!!
                $_stats = [
                    'uri'         => $_instance->getProvisionedEndpoint(),
                    'resources'   => [],
                    'environment' => ['version' => null, 'date' => null, 'status' => null],
                ];

                if (false === ($_status = $_api->determineInstanceState(true))) {
                    throw new InstanceNotActivatedException($_instance->instance_id_text);
                }

                array_set($_stats, 'environment.version', data_get($_status, 'platform.version_current'));

                //  Does it appear ok?
                $_instance->fresh();

                switch ($_instance->ready_state_nbr) {
                    case InstanceStates::READY:
                        \Log::debug('[dfe.usage-service:gatherInstanceStatistics] active ' . $_instance->instance_id_text);
                        array_set($_stats, 'environment.status', 'activated');
                        break;

                    case InstanceStates::ADMIN_REQUIRED:
                        \Log::debug('[dfe.usage-service:gatherInstanceStatistics] no admin ' . $_instance->instance_id_text);
                        array_set($_stats, 'environment.status', 'no admin');
                        break;

                    case InstanceStates::INIT_REQUIRED:
                    default:
                        \Log::debug('[dfe.usage-service:gatherInstanceStatistics] inactive ' . $_instance->instance_id_text);
                        //  Just skip the resource attempts entirely if this guy isn't activated
                        throw new InstanceNotActivatedException($_instance->instance_id_text);
                }

                //  Resources
                if (false === ($_stats['resources'] = $this->getResourceCounts($_api))) {
                    \Log::debug('[dfe.usage-service:gatherInstanceStatistics] -> ' . $_instance->instance_id_text . ' has no resources');
                    $_stats['resources'] = [];
                }
            } catch (InstanceNotActivatedException $_ex) {
                //  Instance unavailable or not initialized
                array_set($_stats, 'environment.status', 'not activated');
            } catch (\Exception $_ex) {
                \Log::debug('[dfe.usage-service:gatherInstanceStatistics] error ' . $_instance->instance_id_text);

                //  Instance unavailable or not initialized
                array_set($_stats, 'environment.status', 'error');
            }

            try {
                if (null === ($_row = $_instance->metrics($_gatherDate))) {
                    $_row = new MetricsDetail();
                    $_row->user_id = $_instance->user_id;
                    $_row->instance_id = $_instance->id;
                }

                $_row->gather_date = $_gatherDate;
                $_row->data_text = $_stats;
                $_row->save();

                $_gathered++;
            } catch (\Exception $_ex) {
                \Log::error('[dfe.usage-service:gatherInstanceStatistics] ' . $_ex->getMessage());
            }

            unset($_stats, $_list, $_status);
        }

        return $this->aggregateStoredMetrics($_gatherDate);

        //  The new way
        //return $this->telemetry->make('instance')->getTelemetry();
    }

    /**
     * @param array $metrics The raw metrics data
     * @param bool  $send    If true, sends anonymous metrics
     *
     * @return mixed
     */
    protected function bundleMetrics(array $metrics, $send = false)
    {
        $_bundle = array_merge([
            'metrics' => [
                'install-key' => $this->installKey,
                'version'     => static::METRICS_VERSION,
                'date'        => date('c'),
            ],
        ],
            $metrics);

        //  Send metrics if wanted
        $send && $this->sendMetrics($_bundle);

        return $_bundle;
    }

    /**
     * @param string|Carbon $date
     *
     * @return array [];
     */
    protected function aggregateStoredMetrics($date)
    {
        $_gathered = $_totals = $_versions = [];
        $_states = ['activated' => 0, 'error' => 0, 'not activated' => 0, 'no admin' => 0];

        //  Pull all the details up into a single array and return it
        /** @noinspection PhpUndefinedMethodInspection */
        foreach (MetricsDetail::byGatherDate($date)->with('instance')->get() as $_detail) {
            $_metrics = $_detail->data_text;
            $_cleaned = [];

            //  Aggregate versions
            $_version = data_get($_metrics, 'environment.version');
            $_version && !array_key_exists($_version, $_versions) && $_versions[$_version] = 0;
            $_version && $_versions[$_version]++;

            //  Aggregate statuses
            $_state = data_get($_metrics, 'environment.status');
            $_state && !array_key_exists($_state, $_states) && $_states[$_state] = 0;
            $_state && $_states[$_state]++;

            //  Aggregate
            foreach (data_get($_metrics, 'resources', []) as $_resource => $_count) {
                !array_key_exists($_resource, $_totals) && $_totals[$_resource] = 0;
                $_cleaned[$_resource] = $_count;
                is_numeric($_count) && $_totals[$_resource] += $_count;
            }

            $_gathered[$_detail->instance->instance_id_text] = array_merge($_metrics, ['resources' => $_cleaned]);
        }

        return array_merge(['_aggregated' => ['versions' => $_versions, 'resources' => $_totals, 'states' => $_states,],], $_gathered);
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

            //  Jam the install key into the root...
            if (!empty($stats) && !is_scalar($stats)) {
                !isset($stats['install-key']) && $stats['install-key'] = $this->installKey;
                $stats = Json::encode($stats);
            }

            $_options = [CURLOPT_HTTPHEADER => ['Content-Type: application/json']];

            try {
                if (false === ($_result = Curl::post($_endpoint, $stats, $_options))) {
                    throw new \RuntimeException('Network error during metrics send.');
                }

                \Log::debug('[dfe.usage-service:sendMetrics] usage data sent to ' . $_endpoint, Curl::getInfo());

                return true;
            } catch (\Exception $_ex) {
                \Log::error('[dfe.usage-service:sendMetrics] exception reporting usage data: ' . $_ex->getMessage());
            }
        } else {
            \Log::notice('[dfe.usage-service:sendMetrics] No usage endpoint found for metrics send.');
        }

        return false;
    }

    /**
     * Iterates through, and counts an instance's resources
     *
     * @param InstanceApiClientService $client
     *
     * @return array
     */
    protected function getResourceCounts($client)
    {
        $_list = [];

        if (false === ($_resources = $client->resources()) || empty($_resources)) {
            return false;
        }

        foreach ($_resources as $_resource) {
            try {
                if (false !== ($_result = $client->resource($_resource))) {
                    $_list[$_resource] = count($_result);
                } else {
                    $_list[$_resource] = 0;
                }
            } catch (\Exception $_ex) {
                $_list[$_resource] = 'error';
            }
        }

        return $_list;
    }
}
