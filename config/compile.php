<?php
//******************************************************************************
//* Pre-compiled class setup
//******************************************************************************
return [
    //  Additional Compiled Classes
    'files'     => [
        realpath(__DIR__ . '/../app/Providers/AppServiceProvider.php'),
        realpath(__DIR__ . '/../app/Providers/BusServiceProvider.php'),
        realpath(__DIR__ . '/../app/Providers/ConfigServiceProvider.php'),
        realpath(__DIR__ . '/../app/Providers/EventServiceProvider.php'),
        realpath(__DIR__ . '/../app/Providers/RouteServiceProvider.php'),
    ],
    //  Compiled File Providers
    'providers' => [
        /** DreamFactory Services service providers */
        DreamFactory\Enterprise\Services\Auditing\AuditServiceProvider::class,
    ],
];
