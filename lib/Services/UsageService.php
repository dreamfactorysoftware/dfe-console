<?php namespace DreamFactory\Enterprise\Services;

use Carbon\Carbon;
use DreamFactory\Enterprise\Common\Enums\InstanceStates;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
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
use DreamFactory\Enterprise\Services\Facades\License;
use DreamFactory\Enterprise\Services\Facades\Telemetry;
use Exception;
use Log;
use ReflectionClass;
use Request;

/**
 * General usage services
 */
class UsageService extends BaseService implements MetricsProvider
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

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

        //  Connect to the license server
        License::connect();
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
        $_send = array_get($options, 'send', false);
        $_metrics = config('telemetry.enabled', false) ? $this->telemetry->getTelemetry() : $this->gatherStatistics();

        return $this->bundleMetrics($_metrics, $_send);
    }

    /**
     * Returns statistics gathered from various sources as defined by the methods in this class and its subclasses
     *
     * @return array
     */
    protected function gatherStatistics()
    {
        //  Set our installation key
        $_stats = [];

        $_mirror = new ReflectionClass(get_called_class());

        foreach ($_mirror->getMethods() as $_method) {
            if (preg_match("/^gather(.+)Statistics$/i", $_methodName = $_method->getShortName())) {
                $_which = str_slug(str_ireplace(['gather', 'statistics'], null, $_methodName));

                //  Call the stats gatherer, don't add if empty
                try {
                    $_result = call_user_func([get_called_class(), $_methodName]);

                    if (!empty($_result)) {
                        $_stats[$_which] = $_result;
                    }

                    unset($_result);
                } catch (Exception $_ex) {
                    Log::error('[dfe.usage-service:gatherStatistics] exception during gather "' . $_methodName . '": ' . $_ex->getMessage());
                }
            }
        }

        //  Move instance aggregate to console
        if (!empty($_aggregate = data_get($_stats, 'instance._aggregated'))) {
            array_set($_stats, 'console.aggregate', $_aggregate);
            array_forget($_stats, 'instance._aggregated');
        }

        //  Remove instance container if details disabled
        !config('license.send-instance-details', false) && array_forget($_stats, 'instance');

        return $_stats;
    }

    /**
     * @return array
     */
    protected function gatherConsoleStatistics()
    {
        $_stats = [
            'uri'       => $_uri = config('app.url', Request::getSchemeAndHttpHost()),
            'resources' => [
                'user'     => ServiceUser::count(),
                'mount'    => Mount::count(),
                'server'   => Server::count(),
                'cluster'  => Cluster::count(),
                'limit'    => Limit::count(),
                'instance' => Instance::count(),
            ],
        ];

        logger('[dfe.usage-service:gatherConsoleStatistics] ** ' . $_uri);

        return $_stats;
    }

    /**
     * @return array
     */
    protected function gatherDashboardStatistics()
    {
        $_stats = [
            'uri'       => $_uri = config('dfe.dashboard-url'),
            'resources' => [
                'user' => User::count(),
            ],
        ];

        logger('[dfe.usage-service:gatherDashboardStatistics] ** ' . $_uri);

        return $_stats;
    }

    /**
     * @param int|null $start The instance id to start with
     *
     * @return array
     */
    protected function gatherInstanceStatistics($start = null)
    {
        $_gatherDate = date('Y-m-d');

        //  Do the non-activated first
        $this->processInstanceStatistics(false, $start);

        //  Then activated
        $this->processInstanceStatistics(true, $start);

        //  Then bundle 'em up.
        return $this->aggregateInstanceMetrics($_gatherDate);
    }

    /**
     * @param bool     $activated If true, only activated instances will be used. If false, non-activated instances will be processed.
     * @param int|null $start     The instance id to start with, for restarting the process from a prior run
     *
     * @return array
     */
    protected function processInstanceStatistics($activated = true, $start = null)
    {
        $_gathered = 0;
        $_gatherDate = date('Y-m-d');

        //  Get a list of all instance pk's
        $_instanceIds = $start
            //  NOT all
            ? \DB::select('SELECT id FROM instance_t WHERE activate_ind = :activate_ind AND id >= :id',
                [':id' => $start, ':activate_ind' => $activated])
            //  All
            : \DB::select('SELECT id FROM instance_t WHERE activate_ind = :activate_ind',
                [':activate_ind' => $activated]);

        /** @type Instance $_instance */
        foreach ($_instanceIds as $_item) {
            $_api = new InstanceApiClientService($this->app);
            $_api->connect($_instance = $this->findInstance($_item->id));

            //  Seed the stats, defaults to "not activated"
            $_stats = [
                'uri'         => $_instance->getProvisionedEndpoint(),
                'environment' => ['version' => null, 'inception' => $_instance->create_date, 'status' => $activated ? 'activated' : 'not activated'],
                'resources'   => [],
            ];

            try {
                if (false !== ($_status = $_api->determineInstanceState(true))) {
                    array_set($_stats, 'environment.version', data_get($_status, 'platform.version_current'));

                    //  Does it appear ok?
                    if (!$activated) {
                        $_instance = $_instance->fresh();

                        switch ($_instance->ready_state_nbr) {
                            case InstanceStates::READY:
                                $_stats['environment']['status'] = 'activated';
                                break;

                            case InstanceStates::ADMIN_REQUIRED:
                                $_stats['environment']['status'] = 'no admin';
                                break;

                            default:
                                $_stats['environment']['status'] = 'not activated';
                                break;
                        }
                    }

                    //  Get resource counts
                    if (InstanceStates::INIT_REQUIRED != $_instance->ready_state_nbr && false === ($_stats['resources'] = $this->getResourceCounts($_api))) {
                        $_stats['resources'] = [];
                    }
                }
            } catch (InstanceNotActivatedException $_ex) {
                //  Instance unavailable or not initialized
            } catch (Exception $_ex) {
                //  Instance unavailable or not initialized
                array_set($_stats, 'environment.status', 'error');
            }

            logger('[dfe.usage-service:instance] > ' . $_stats['environment']['status'] . ' ' . $_instance->id . ':' . $_instance->instance_id_text);

            try {
                /** @type MetricsDetail $_row */
                $_row = MetricsDetail::firstOrCreate(['user_id' => $_instance->user_id, 'instance_id' => $_instance->id, 'gather_date' => $_gatherDate]);
                $_row->data_text = $_stats;
                $_row->save();

                $_gathered++;
            } catch (Exception $_ex) {
                Log::error('[dfe.usage-service:instance] ' . $_ex->getMessage());
            }

            //  Let it go
            $_api->disconnect();

            unset($_api, $_stats, $_list, $_status, $_row, $_instance, $_instanceId);
        }

        Log::info('[dfe.usage-service:instance] ' . number_format($_gathered, 0) . ' instance(s) examined.');
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
                'install-key' => License::getInstallKey(),
                'version'     => static::METRICS_VERSION,
                'date'        => date('c'),
            ],
        ],
            $metrics);

        //  Send metrics if wanted
        $send && License::reportStatistics($_bundle);

        return $_bundle;
    }

    /**
     * @param string|Carbon $date
     *
     * @return array [];
     */
    protected function aggregateInstanceMetrics($date)
    {
        $_detailed = config('license.send-instance-details', false);
        $_gathered = $_resourceCounts = $_versions = $_states = [];

        //  Pull all the details up into a single array and return it
        /** @noinspection PhpUndefinedMethodInspection */
        foreach (MetricsDetail::byGatherDate($date)->with('instance')->get() as $_detail) {
            $_metrics = $_detail->data_text;

            //  Aggregate versions
            if (!empty($_version = data_get($_metrics, 'environment.version'))) {
                !array_key_exists($_version, $_versions) && $_versions[$_version] = 0;
                $_versions[$_version]++;
            }

            //  Aggregate statuses
            if (!empty($_state = data_get($_metrics, 'environment.status'))) {
                !array_key_exists($_state, $_states) && $_states[$_state] = 0;
                $_states[$_state]++;
            }

            //  Aggregate resource counts
            foreach (data_get($_metrics, 'resources', []) as $_resource => $_count) {
                !array_key_exists($_resource, $_resourceCounts) && $_resourceCounts[$_resource] = 0;
                is_numeric($_count) && $_resourceCounts[$_resource] += $_count;
            }

            $_detailed && $_gathered[$_detail->instance->instance_id_text] = $_metrics;
        }

        return array_merge($_gathered,
            [
                '_aggregated' => [
                    'versions'  => $_versions,
                    'resources' => $_resourceCounts,
                    'states'    => $_states,
                ],
            ]);
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
            } catch (Exception $_ex) {
                $_list[$_resource] = 'error';
            }
        }

        return $_list;
    }

    /**
     * Retrieves the current instance database connection
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection()
    {
        static $_db;

        return $_db ?: $_db = $this->instance->instanceConnection();
    }
}
