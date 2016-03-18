<?php namespace DreamFactory\Enterprise\Services\Telemetry;

use DreamFactory\Enterprise\Common\Exceptions\NotImplementedException;
use DreamFactory\Enterprise\Services\Contracts\ProvidesTelemetry;

abstract class BaseTelemetryProvider implements ProvidesTelemetry
{
    //******************************************************************************
    //* Constant
    //******************************************************************************

    /**
     * @type string Our provider ID
     */
    const PROVIDER_ID = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    abstract public function getTelemetry($options = []);

    /**
     * @return string
     * @throws \DreamFactory\Enterprise\Common\Exceptions\NotImplementedException
     */
    public function getProviderId()
    {
        if (!static::PROVIDER_ID) {
            throw new NotImplementedException('The PROVIDER_ID must be overridden in your sub-class.');
        }

        return static::PROVIDER_ID;
    }
}
