<?php
//******************************************************************************
//* Capsule-related settings
//******************************************************************************

use DreamFactory\Enterprise\Instance\Capsule\Enums\CapsuleDefaults;

return [
    /** The root path to all capsules */
    'root-path' => env('DFE_CAPSULE_ROOT_PATH', CapsuleDefaults::DEFAULT_ROOT_PATH),
    /** Instance settings */
    'instance'  => [
        /** The "instance" installation path on the cluster */
        'install-path' => env('DFE_INSTANCE_INSTALL_PATH', CapsuleDefaults::DEFAULT_INSTANCE_INSTALL_PATH),
        /** The "storage-path" link name */
        'storage-path' => 'storage',
        /** Directories/files which can be symlinked */
        'symlinks'     => [
            'app',
            'artisan',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources',
            'storage',
            'vendor',
        ],
    ],
];
