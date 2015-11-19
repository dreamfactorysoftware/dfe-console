<?php namespace DreamFactory\Enterprise\Services\Contracts;

use DreamFactory\Enterprise\Common\Contracts\PortabilityAware;

/**
 * Something that provides telemetry
 */
interface ProvidesTelemetry
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Returns any gathered telemetry
     *
     * @param array|null $options Array of options for gathering
     *
     * @return mixed|array
     */
    public function getTelemetry($options = []);
}
