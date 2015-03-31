<?php
return [
    //  Default Filesystem Disk
    'default' => 'local',
    //  Default Cloud Filesystem Disk
    'cloud'   => 's3',
    //  Filesystem Disks
    'disks'   => [
        'local'  => [
            'driver' => 'local',
            'root'   => storage_path() . '/app',
        ],
        //  hosted storage
        'hosted' => [
            'driver' => 'local',
            'root'   => env( 'DFE_HOSTED_BASE_PATH', '/data/storage' ),
        ]
    ],
];
