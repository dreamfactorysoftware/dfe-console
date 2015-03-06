<?php

return [
    //  Default queue driver
    'default'     => env( 'QUEUE_DRIVER', 'sync' ),
    //  Connections
    'connections' => [
        'sync'     => [
            'driver' => 'sync',
        ],
        'database' => [
            'driver'   => 'database',
            'table'    => 'job_t',
            'queue'    => 'default',
            'expire'   => 60,
            'database' => 'dfe-local',
        ],
    ],
    //  Failed jobs
    'failed'      => [
        'database' => 'dfe-local',
        'table'    => 'job_fail_t',
    ],
];
