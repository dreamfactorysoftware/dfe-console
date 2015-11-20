<?php namespace DreamFactory\Enterprise\Services\Telemetry;

class DashboardTelemetry extends BaseTelemetryProvider
{
    //******************************************************************************
    //* Constant
    //******************************************************************************

    /**
     * @type string Our provider ID
     */
    const PROVIDER_ID = 'dashboard';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function getTelemetry($options = [])
    {
        return [];
    }
}
