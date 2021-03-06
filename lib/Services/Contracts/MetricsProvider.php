<?php namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * Something that provides over-all metrics
 */
interface MetricsProvider
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Retrieves the metrics
     *
     * @param array|null $options Any options around providing the data
     *
     * @return mixed|array
     */
    public function getMetrics($options = []);
}
