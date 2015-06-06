<?php

//******************************************************************************
//* Master DFE Console Settings
//******************************************************************************

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;

return [
    //******************************************************************************
    //* General
    //******************************************************************************
    //  The id of THIS cluster
    'cluster-id'        => env( 'DFE_CLUSTER_ID' ),
    //  A string to be pre-pended to instance names for non-admin users
    'instance-prefix'   => env( 'DFE_DEFAULT_INSTANCE_PREFIX' ),
    'signature-method'  => env( 'DFE_SIGNATURE_METHOD', EnterpriseDefaults::DEFAULT_SIGNATURE_METHOD ),
    //******************************************************************************
    //* Common settings across all app
    //******************************************************************************
    'common'            => [
        'display-name'      => 'DreamFactory Enterprise&trade; Console',
        'display-version'   => 'v1.0.x-alpha',
        'display-copyright' => '© DreamFactory Software, Inc. 2012-' . date( 'Y' ) . '. All Rights Reserved.',
        /**
         * Theme selection -- a bootswatch theme name
         * Included are cerulean, darkly, flatly, paper, and superhero.
         * You may also install other compatible themes and use them as well.
         */
        'themes'            => ['auth' => 'darkly', 'page' => 'flatly'],
    ],
    //******************************************************************************
    //* Provisioners
    //******************************************************************************
    'provisioners'      => [
        //  The default provisioner
        'default' => 'rave',
        //  The supported provisioners/hosts
        'hosts'   => [
            'rave' => [
                /** Our sub-provisioners */
                'instance'  => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\Provisioner',
                'storage'   => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\StorageProvisioner',
                'db'        => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\DatabaseProvisioner',
                /** Our offerings */
                'offerings' => [
                    'instance-version' => [
                        'name'       => 'Version',
                        'help-block' => 'If you wish, you may choose a different version of the DSP to provision.',
                        'suggested'  => '1.10.x-dev',
                        'items'      => [
                            '1.10.x-dev' => ['document-root' => '/var/www/_releases/dsp-core/1.9.x-dev/web', 'description' => 'DSP v1.10.x-dev',],
                        ],
                    ],
                ],
            ],
        ],
    ],
    //******************************************************************************
    //* Provisioning Defaults
    //******************************************************************************
    'provisioning'      => [
        //******************************************************************************
        //* Storage & storage layout options/settings
        //******************************************************************************
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
        //******************************************************************************
        //* Instance provisioning defaults
        //******************************************************************************
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
        //  Ubuntu server 12.04.1 i386
        'default-vendor-image-id'     => 4647,
        //	i386
        'default-vendor-image-flavor' => 0,
    ],
    //******************************************************************************
    //* Console API Keys
    //******************************************************************************
    'security'          => [
        'console-api-url'           => env( 'DFE_CONSOLE_API_URL' ),
        /** This key needs to match the key configured in the dashboard */
        'console-api-key'           => env( 'DFE_CONSOLE_API_KEY' ),
        'console-api-client-id'     => env( 'DFE_CONSOLE_API_CLIENT_ID' ),
        'console-api-client-secret' => env( 'DFE_CONSOLE_API_CLIENT_SECRET' ),
    ],
    //******************************************************************************
    //* Individual command settings
    //******************************************************************************
    'commands'          => [
        'setup' => [
            'display-name'         => 'DreamFactory Enterprise Setup and Initialization',
            'display-version'      => 'v1.0.x-alpha',
            'display-copyright'    => 'Copyright (c) 2012-' . date( 'Y' ) . ', All Rights Reserved',
            /** Necessary directory structure and modes */
            'required-directories' => [
                'storage/framework/cache',
                'storage/framework/sessions',
                'storage/framework/views',
                'storage/logs',
            ],
        ],
    ],
    //******************************************************************************
    //* Forbidden instance names
    //******************************************************************************
    'forbidden-names'   => [
        /** reserved */
        'dreamfactory',
        'dream',
        'factory',
        'developer',
        'wiki',
        'enterprise',
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
        'email',
        'rave',
        'console',
        'dashboard',
        'launchpad',
        /** icky */
        'feces',
        'fecal',
        'defecate',
        'urinate',
        'inseminate',
        'cum',
        'jizz',
        'semen',
        /** The holy seven... */
        'shit',
        'piss',
        'fuck',
        'cunt',
        'cocksucker',
        'motherfucker',
        'tits',
    ],
];