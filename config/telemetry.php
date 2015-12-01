<?php
use DreamFactory\Enterprise\Services\Telemetry\ConsoleTelemetry;
use DreamFactory\Enterprise\Services\Telemetry\DashboardTelemetry;
use DreamFactory\Enterprise\Services\Telemetry\InstanceTelemetry;

//******************************************************************************
//* DFE Telemetry Settings
//******************************************************************************

return [
    //******************************************************************************
    //* Options
    //******************************************************************************
    'enabled'   => false,
    //******************************************************************************
    //* The various providers which are available
    //******************************************************************************
    'providers' => [
        'console'   => ConsoleTelemetry::class,
        'dashboard' => DashboardTelemetry::class,
        'instance'  => InstanceTelemetry::class,
    ],
];
