<?php
return [
    //  Default connection
    'default'     => 'local',
    //  Connections
    'connections' => [
        'local' => [
            'driver' => 'local',
            'path'   => env( 'DFE_HOSTED_BASE_PATH', '/data/storage' ),
        ],
    ],
    //  Cache
    'cache'       => [
        'foo'     => [
            'driver'    => 'illuminate',
            'connector' => null, // null means use default driver
            'key'       => 'foo',
            // 'ttl'       => 300
        ],
        'bar'     => [
            'driver'    => 'illuminate',
            'connector' => 'redis', // config/cache.php
            'key'       => 'bar',
            'ttl'       => 600,
        ],
        'adapter' => [
            'driver'  => 'adapter',
            'adapter' => 'local', // as defined in connections
            'file'    => 'flysystem.json',
            'ttl'     => 600,
        ],
    ],
];
