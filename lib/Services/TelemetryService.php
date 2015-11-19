<?php namespace DreamFactory\Enterprise\Services;

use Carbon\Carbon;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Enterprise\Database\Models\Mount;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\Telemetry;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Instance\Ops\Facades\InstanceApiClient;
use DreamFactory\Enterprise\Services\Contracts\ProvidesTelemetry;
use DreamFactory\Enterprise\Services\Contracts\TelemetryAggregator;
use DreamFactory\Enterprise\Services\Providers\TelemetryServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * A pluggable generic telemetry service
 */
class TelemetryService extends BaseService implements TelemetryAggregator
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string[] The various registered telemetry providers
     */
    protected $providers = [];
    /**
     * @type ProvidesTelemetry[] array The provider instances
     */
    protected $instances = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Registers a new telemetry source for aggregation
     *
     * @param string            $name     Provider name
     * @param ProvidesTelemetry $provider The provider
     *
     * @return static
     */
    public function registerProvider($name, $provider)
    {
        if (!in_array($name, $this->providers)) {
            $this->providers[] = $name;

            if ($provider instanceof ProvidesTelemetry || $provider instanceof \Closure) {
                $this->instances[$name] = $provider;
            } else if (is_string($provider) && class_exists($provider, false)) {
                $this->instances[$name] = new $provider();
            }
        }

        return $this;
    }

    /**
     * Registers a new telemetry source for aggregation
     *
     * @param string $name Provider name
     *
     * @return bool True if provider exists and was removed
     */
    public function unregisterProvider($name)
    {
        if (in_array($name, $this->providers)) {
            $this->instances[$name] = null;
            array_forget($this->instances, $name);

            return true;
        }

        return false;
    }

    /**
     * Resolves an instance of the provider by $name
     *
     * @param string $name Provider name
     *
     * @return ProvidesTelemetry
     */
    public function make($name)
    {
        if (!isset($this->instances, $this->instances[$name])) {
            throw new \InvalidArgumentException('The provider "' . $name . '" is not registered.');
        }

        return $this->instances[$name];
    }

    /**
     * Returns an array, indexed by provider name, of telemetry data gathered during the period defined by $start to $end
     *
     * @param string|array|null $providers The providers to query
     * @param string|int|null   $start     The period start (greater or equal to >=). Null == today
     * @param string|int|null   $end       The period end (less than <). Null == today
     * @param bool              $store     If true, the response is stored for posterity
     *
     * @return array
     */
    public function getTelemetry($providers = null, $start = null, $end = null, $store = true)
    {
        $_providers = $providers ?: $this->providers;
        is_string($_providers) && $_providers = [$_providers];

        //  Identify our data
        $_telemetry = [
            '_meta' => [
                'source-uri'        => config('app.url', \Request::getSchemeAndHttpHost()),
                'request-timestamp' => Carbon::now(),
                'request-host'      => 'cli' == PHP_SAPI ? 'localhost' : \Request::getSchemeAndHttpHost(),
                'request-ip'        => 'cli' == PHP_SAPI ? ['127.0.0.1'] : \Request::getClientIps(),
            ],
        ];

        foreach ($_providers as $_provider) {
            $_telemetry[$_provider] = $this->make($_provider)->getTelemetry();
        }

        $store && Telemetry::storeTelemetry(TelemetryServiceProvider::IOC_NAME, $_telemetry);

        return is_string($providers) ? current($_telemetry) : $_telemetry;
    }
}
