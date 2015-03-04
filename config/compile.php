<?php
return [
    //  Additional Compiled Classes
    'files'     => [
        realpath( __DIR__ . '/../src/Providers/AppServiceProvider.php' ),
        realpath( __DIR__ . '/../src/Providers/BusServiceProvider.php' ),
        realpath( __DIR__ . '/../src/Providers/ConfigServiceProvider.php' ),
        realpath( __DIR__ . '/../src/Providers/EventServiceProvider.php' ),
        realpath( __DIR__ . '/../src/Providers/RouteServiceProvider.php' ),
    ],
    //  Compiled File Providers
    'providers' => [],
];
