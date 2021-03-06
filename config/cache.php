<?php
//******************************************************************************
//* Application Cache Settings
//******************************************************************************
return [
    'default' => env('CACHE_DRIVER', 'file'),
    'prefix'  => 'dfe-console',
    'stores'  => [
        'array'     => [
            'driver' => 'array',
        ],
        'database'  => [
            'driver'     => 'database',
            'table'      => 'cache_t',
            'connection' => 'dfe-local',
        ],
        'file'      => [
            'driver' => 'file',
            'path'   => base_path() . '/bootstrap/cache',
        ],
        'memcached' => [
            'driver'  => 'memcached',
            'servers' => [
                [
                    'host'   => '127.0.0.1',
                    'port'   => 11211,
                    'weight' => 100,
                ],
            ],
        ],
        'redis'     => [
            'driver'     => 'redis',
            'connection' => 'default',
        ],
    ],
];
