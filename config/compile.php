<?php
return [
    //  Additional Compiled Classes
    'files'     => [
        realpath( __DIR__ . '/../app/Providers/AppServiceProvider.php' ),
        realpath( __DIR__ . '/../app/Providers/BusServiceProvider.php' ),
        realpath( __DIR__ . '/../lib/Common/Providers/ConfigServiceProvider.php' ),
        realpath( __DIR__ . '/../lib/Common/Providers/EventServiceProvider.php' ),
        realpath( __DIR__ . '/../app/Providers/RouteServiceProvider.php' ),
    ],
    //  Compiled File Providers
    'providers' => [
        realpath( __DIR__ . '/../app/Providers/ElkServiceProvider.php' ),
    ],
];
