<?php
//******************************************************************************
//* File systems used by DFE Console
//******************************************************************************
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

return [
    //  Default Filesystem Disk
    'default' => 'local',
    //  Default Cloud Filesystem Disk
    'cloud'   => 's3',
    //  Filesystem Disks
    'disks'   => [
        'local'            => [
            'driver'      => 'local',
            'root'        => storage_path() . '/app',
            'permissions' => [
                'file' => [
                    'public'  => 0764,
                    'private' => 0700,
                ],
                'dir'  => [
                    'public'  => 2775,
                    'private' => 0700,
                ],
            ],
        ],
        //  cluster-east-2 hosted storage
        'cluster-east-2'   => [
            'driver'      => 'local',
            'root'        => env('DFE_HOSTED_BASE_PATH', ConsoleDefaults::STORAGE_ROOT),
            'permissions' => [
                'file' => [
                    'public'  => 0764,
                    'private' => 0700,
                ],
                'dir'  => [
                    'public'  => 2775,
                    'private' => 0700,
                ],
            ],
        ],
        //  mount-east-1 hosted storage
        'mount-east-1'     => [
            'driver'      => 'local',
            'root'        => env('DFE_HOSTED_BASE_PATH', ConsoleDefaults::STORAGE_ROOT),
            'permissions' => [
                'file' => [
                    'public'  => 0764,
                    'private' => 0700,
                ],
                'dir'  => [
                    'public'  => 2775,
                    'private' => 0700,
                ],
            ],
        ],
        //  dfe-mount-east-1 hosted storage
        'dfe-mount-east-1' => [
            'driver'      => 'local',
            'root'        => env('DFE_HOSTED_BASE_PATH', ConsoleDefaults::STORAGE_ROOT),
            'permissions' => [
                'file' => [
                    'public'  => 0764,
                    'private' => 0700,
                ],
                'dir'  => [
                    'public'  => 2775,
                    'private' => 0700,
                ],
            ],
        ],
        'mount-local-1'    => [
            'driver'      => 'local',
            'root'        => env('DFE_HOSTED_BASE_PATH', ConsoleDefaults::STORAGE_ROOT),
            'permissions' => [
                'file' => [
                    'public'  => 0764,
                    'private' => 0700,
                ],
                'dir'  => [
                    'public'  => 2775,
                    'private' => 0700,
                ],
            ],
        ],
    ],
];
