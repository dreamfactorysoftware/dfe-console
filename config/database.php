<?php
//******************************************************************************
//* Database configuration
//******************************************************************************
return [
    'fetch'       => PDO::FETCH_CLASS,
    'default'     => 'dfe-local',
    'migrations'  => 'migration_t',
    //******************************************************************************
    //* Connections
    //******************************************************************************
    'connections' => [
        /** dfe-local is the main console/dashboard database */
        'dfe-local'  => [
            'driver'    => env('DB_DRIVER', 'mysql'),
            'host'      => env('DB_HOST', 'localhost'),
            'port'      => env('DB_PORT', 3306),
            'database'  => env('DB_DATABASE', 'dfe_local'),
            'username'  => env('DB_USERNAME', 'dfe_user'),
            'password'  => env('DB_PASSWORD', 'dfe_user'),
            'charset'   => env('DB_CHARSET', 'utf8'),
            'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
            'prefix'    => env('DB_PREFIX'),
        ],
        /** dfe-local is the main console/dashboard database */
        'dfe-remote' => [
            'driver'    => env('DB_REMOTE_DRIVER', 'mysql'),
            'host'      => env('DB_REMOTE_HOST', 'localhost'),
            'port'      => env('DB_REMOTE_PORT', 3306),
            'database'  => env('DB_REMOTE_DATABASE', 'dfe_local'),
            'username'  => env('DB_REMOTE_USERNAME', 'dfe_user'),
            'password'  => env('DB_REMOTE_PASSWORD', 'dfe_user'),
            'charset'   => env('DB_REMOTE_CHARSET', 'utf8'),
            'collation' => env('DB_REMOTE_COLLATION', 'utf8_unicode_ci'),
            'prefix'    => env('DB_REMOTE_PREFIX'),
        ],
    ],
    'redis'       => [
        'cluster' => false,
        'default' => [
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'database' => 0,
        ],
    ],
];
