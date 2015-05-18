<?php
//******************************************************************************
//* DFE Console Specific Settings
//******************************************************************************

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;

return [
    'console-api-url'  => 'http://console.enterprise.dreamfactory.com/api/v1/ops/',
    /** This key needs to match the key configured in the dashboard */
    'console-api-key'  => env( 'DFE_CONSOLE_API_KEY', '%]3,]~&t,EOxL30[wKw3auju:[+L>eYEVWEP,@3n79Qy' ),
    'signature-method' => env( 'DFE_SIGNATURE_METHOD', 'sha256' ),
    'provisioning'     => [
        /**
         * Storage & storage layout options/settings
         */
        //  The root path of where instances' data will live
        'storage-root'                => env( 'DFE_STORAGE_BASE_PATH', ConsoleDefaults::STORAGE_ROOT ),
        //  Either "static" or "dynamic"
        'storage-zone-type'           => env( 'DFE_STORAGE_ZONE_TYPE', 'static' ),
        //  The "static" storage zone
        'static-zone-name'            => env( 'DFE_STATIC_ZONE_NAME', 'ec2.us-east-1a' ),
        //  relative to storage path (hosted or non)
        'public-path-base'            => env( 'DFE_PUBLIC_PATH_BASE', '/' ),
        //  relative to storage path (hosted or non)
        'private-path-name'           => env( 'DFE_PRIVATE_PATH_NAME', ConsoleDefaults::PRIVATE_PATH_NAME ),
        // relative to owner-private-path
        'snapshot-path-name'          => env( 'DFE_SNAPSHOT_PATH_NAME', ConsoleDefaults::SNAPSHOT_PATH_NAME ),
        /** "DFE_*_PATHS" variables can contain one or more, pipe-delimited names of directories to create */
        'public-paths'                => explode( '|', env( 'DFE_PUBLIC_PATHS', 'applications|.private' ) ),
        'private-paths'               => explode( '|', env( 'DFE_PRIVATE_PATHS', '.cache|config|scripts|scripts.user|log' ) ),
        'owner-private-paths'         => explode( '|', env( 'DFE_OWNER_PRIVATE_PATHS', ConsoleDefaults::SNAPSHOT_PATH_NAME ) ),
        //  Instance provisioning defaults
        'default-cluster-id'          => env( 'DFE_DEFAULT_CLUSTER' ),
        'default-db-server-id'        => env( 'DFE_DEFAULT_DATABASE' ),
        'default-guest-location'      => env( 'DFE_DEFAULT_GUEST_LOCATION', GuestLocations::DFE_CLUSTER ),
        'default-ram-size'            => env( 'DFE_DEFAULT_RAM_SIZE', 1 ),
        'default-disk-size'           => env( 'DFE_DEFAULT_DISK_SIZE', 8 ),
        //  Notification settings
        'email-subject-prefix'        => env( 'DFE_EMAIL_SUBJECT_PREFIX', '[DFE]' ),
        //  Instance defaults
        'default-dns-zone'            => env( 'DFE_DEFAULT_DNS_ZONE' ),
        'default-dns-domain'          => env( 'DFE_DEFAULT_DNS_DOMAIN' ),
        'default-domain'              => env( 'DFE_DEFAULT_DOMAIN' ),
        //@todo update image to 14.* LTS x64
        'default-vendor-image-id'     => 4647,
        //	Ubuntu server 12.04.1 i386
        'default-vendor-image-flavor' => 0,
        //	i386
        //  Disallowed instance names
        'forbidden-names'             => [
            'dreamfactory',
            'dream',
            'factory',
            'developer',
            'wiki',
            'cloud',
            'www',
            'fabric',
            'api',
            'db',
            'database',
            'dsp',
            'dfe',
            'dfac',
            'df',
            'dfab',
            'dfdsp',
            'rave',
            /** The holy seven... */
            'shit',
            'piss',
            'fuck',
            'cunt',
            'cocksucker',
            'motherfucker',
            'tits',
        ],
    ],
];
