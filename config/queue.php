<?php
return [
    //  Default Queue Driver
    'default'     => env( 'QUEUE_DRIVER', 'sync' ),
    //  Queue Connections
    'connections' => [
        'sync'     => [
            'driver' => 'sync',
        ],
        'database' => [
            'driver' => 'database',
            'table'  => 'job_t',
            'queue'  => 'default',
            'expire' => 60,
        ],
        'iron'     => [
            'driver'  => 'iron',
            'host'    => 'mq-aws-us-east-1.iron.io',
            'token'   => 'your-token',
            'project' => 'your-project-id',
            'queue'   => 'your-queue-name',
            'encrypt' => true,
        ],
    ],
    //  Failed Queue Jobs
    'failed'      => [
        'database' => 'mysql',
        'table'    => 'job_fail_t',
    ],
];
