<?php
//******************************************************************************
//* Flysystems used by DFE Console (takes precedence over filesystems.php)
//******************************************************************************
use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;

return [
    //  Default connection
    'default'     => 'local',
    //  Connections
    'connections' => [
        /**
         * DFE expected connections
         */
        'local'            => [
            'driver' => 'local',
            'path'   => env('DFE_HOSTED_BASE_PATH', EnterpriseDefaults::STORAGE_ROOT),
        ],
        //  Alias of "local"
        'mount-local-1'    => [
            'driver' => 'local',
            'path'   => env('DFE_HOSTED_BASE_PATH', EnterpriseDefaults::STORAGE_ROOT),
        ],
        //  Snapshot trash area
        'snapshot-trash'   => [
            'driver' => 'local',
            'path'   => env('DFE_SNAPSHOT_TRASH_PATH', EnterpriseDefaults::DEFAULT_TRASH_PATH),
        ],
        //******************************************************************************
        //* The rest are installation-specific and/or exemplary
        //******************************************************************************
        //  cluster-east-2 hosted storage
        'cluster-east-2'   => [
            'driver' => 'local',
            'path'   => env('DFE_HOSTED_BASE_PATH', EnterpriseDefaults::STORAGE_ROOT),
        ],
        //  mount-east-1 hosted storage
        'mount-east-1'     => [
            'driver' => 'local',
            'path'   => env('DFE_HOSTED_BASE_PATH', EnterpriseDefaults::STORAGE_ROOT),
        ],
        //  dfe-mount-east-1 hosted storage
        'dfe-mount-east-1' => [
            'driver' => 'local',
            'path'   => env('DFE_HOSTED_BASE_PATH', EnterpriseDefaults::STORAGE_ROOT),
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
