<?php
//******************************************************************************
//* Capsule-related settings
//******************************************************************************

use DreamFactory\Enterprise\Instance\Capsule\Enums\CapsuleDefaults;

return [
    /** The root path to all capsules */
    'root-path' => env('DFE_CAPSULE_PATH', CapsuleDefaults::DEFAULT_PATH),
    /** The root path to all capsule logs */
    'log-path'  => env('DFE_CAPSULE_LOG_PATH', CapsuleDefaults::DEFAULT_LOG_PATH),
    /** Instance settings */
    'instance'  => [
        /** The "instance" installation path on the cluster */
        'install-path' => env('DFE_INSTANCE_INSTALL_PATH', CapsuleDefaults::DEFAULT_INSTANCE_INSTALL_PATH),
        /** The "storage-path" link name */
        'storage-path' => 'storage',
        /** The location of the bootstrap files, relative to app base path */
        'bootstrap'    => 'resources/bootstrap',
        /** Directories/files which can be symlinked */
        'symlinks'     => [
            'app',
            'artisan',
            'config',
            'database',
            'public',
            'resources',
            'storage',
            'vendor',
        ],
    ],
];
