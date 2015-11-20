<?php namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * Something that aggregates telemetry
 */
interface TelemetryAggregator
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Registers a new telemetry source for aggregation
     *
     * @param string            $name     Provider name
     * @param ProvidesTelemetry $provider The provider
     *
     * @return static
     */
    public function registerProvider($name, $provider);

    /**
     * Resolves an instance of the provider by $name
     *
     * @param string $name Provider name
     *
     * @return ProvidesTelemetry
     */
    public function make($name);

    /**
     * Returns an array, indexed by provider name, of telemetry data gathered during the period defined by $start to $end
     *
     * @param string|array|null $providers Zero or more providers to get telemetry from. Null == all registered
     * @param string|int|null   $start     The period start (greater or equal to >=). Null == live
     * @param string|int|null   $end       The period end (less than <). Null == live
     * @param bool              $store     If true, the response is stored for posterity
     *
     * @return array
     */
    public function getTelemetry($providers = null, $start = null, $end = null, $store = true);
}
