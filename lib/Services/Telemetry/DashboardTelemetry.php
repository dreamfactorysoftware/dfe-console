<?php namespace DreamFactory\Enterprise\Services\Telemetry;

use DreamFactory\Enterprise\Database\Models\User;

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
        $_stats = [
            'uri'       => $_uri = config('dfe.dashboard-url'),
            'resources' => [
                'user' => User::count(),
            ],
        ];

        logger('[dfe.telemetry.dashboard] ** ' . $_uri);

        return $_stats;
    }
}
