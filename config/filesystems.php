<?php
return [
    //  Default Filesystem Disk
    'default' => 'local',
    //  Default Cloud Filesystem Disk
    'cloud'   => 's3',
    //  Filesystem Disks
    'disks'   => [
        'local'          => [
            'driver' => 'local',
            'root'   => storage_path() . '/app',
        ],
        //  cluster-east-1 hosted storage
        'cluster-east-1' => [
            'driver' => 'local',
            'root'   => env( 'DFE_HOSTED_BASE_PATH', '/data/storage' ),
        ],
        //  mount-east-1 hosted storage
        'mount-east-1'   => [
            'driver' => 'local',
            'root'   => env( 'DFE_HOSTED_BASE_PATH', '/data/storage' ),
        ],
        'mount-local-1'  => [
            'disk' => 'mount-local-1',
            'root' => env( 'DFE_HOSTED_BASE_PATH', '/data/storage' ),
        ],
    ],
];
