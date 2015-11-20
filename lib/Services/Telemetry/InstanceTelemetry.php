<?php namespace DreamFactory\Enterprise\Services\Telemetry;

class InstanceTelemetry extends BaseTelemetryProvider
{
    //******************************************************************************
    //* Constant
    //******************************************************************************

    /**
     * @type string Our provider ID
     */
    const PROVIDER_ID = 'instance';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function getTelemetry($options = [])
    {
        return [];
    }
}
