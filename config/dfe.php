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
    'cluster-id' => env('DFE_CLUSTER_ID'),
    //  A string to be pre-pended to instance names for non-admin users
    'instance-prefix' => env('DFE_DEFAULT_INSTANCE_PREFIX'),
    //  The hash algorithm for hashing api keys. Defaults to 'sha256'
    'signature-method' => env('DFE_SIGNATURE_METHOD', EnterpriseDefaults::DEFAULT_SIGNATURE_METHOD),
    //******************************************************************************
    //* Common settings across portions of app
    //******************************************************************************
    'common' => [
        'display-name' => 'DreamFactory Enterprise&trade; Console',
        'display-version' => 'v1.0.x-alpha',
        'display-copyright' => '© DreamFactory Software, Inc. 2012-' . date('Y') . '. All Rights Reserved.',
        /**
         * Theme selection -- a bootswatch theme name
         * Included are cerulean, darkly, flatly, paper, and superhero.
         * You may also install other compatible themes and use them as well.
         */
        'themes' => ['auth' => 'darkly', 'page' => 'flatly'],
    ],
    //******************************************************************************
    //* The provisioners available from this console
    //******************************************************************************
    'provisioners' => [
        //  The default provisioner
        'default' => 'rave',
        //  The provisioners, or "hosts" of our instances, or "guests".
        'hosts' => [
            /** RAVE = DSP2 */
            'rave' => [
                /********************************************************************************
                 * Each provisioner has a main "instance" provisioning class. In addition there
                 * are two sub-provisioners required.
                 *
                 * The first is "storage" which is the class responsible for instance storage
                 * provisioning.
                 *
                 * The second is "db", or the class/service responsible for instance database
                 * provisioning.
                 *
                 * Currently, all three are required. However, even though required, no actions
                 * need to be performed (if unnecessary, for example).
                 ********************************************************************************/
                //  The main provisioner of the instance
                'instance' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\Provisioner',
                //  The instance's storage provisioner
                'storage' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\StorageProvisioner',
                //  The instance's database provisioner
                'db' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\DatabaseProvisioner',
                /********************************************************************************
                 * Provisioners may have "offerings" or options that dictate certain features of
                 * the available guest(s). Selecting a version for instance (as below). It can be
                 * used for anything and provides an automatic UI in the Dashboard for user selection.
                 ********************************************************************************/
                /** Our offerings */
                'offerings' => [
                    //  An "id" for this offering
                    'instance-version' => [
                        //  The display (label) name to show on the UI
                        'name' => 'Version',
                        //  Any text you wish displayed below the selection (i.e. help text, explanation, etc.)
                        'help-block' => 'If you wish, you may choose a different version of the DSP to provision.',
                        //  The item in the below list of items to pre-select for the user.
                        'suggested' => '1.10.x-dev',
                        //  The list of items to show for this offering.
                        'items' => [
                            '1.10.x-dev' => [
                                'document-root' => '/var/www/_releases/dsp-core/1.9.x-dev/web',
                                'description' => 'DSP v1.10.x-dev',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    //******************************************************************************
    //* Provisioning Defaults
    //******************************************************************************
    'provisioning' => [
        //******************************************************************************
        //* Storage & storage layout options/settings
        //******************************************************************************
        //  The root path of where instances' data will live
        'storage-root' => env('DFE_HOSTED_BASE_PATH', base_path()),
        //  Either "static" or "dynamic"
        'storage-zone-type' => env('DFE_STORAGE_ZONE_TYPE', 'static'),
        //  The "static" storage zone
        'static-zone-name' => env('DFE_STATIC_ZONE_NAME', 'ec2.us-east-1a'),
        //  relative to storage path (hosted or non)
        'public-path-base' => env('DFE_PUBLIC_PATH_BASE', '/'),
        //  relative to storage path (hosted or non)
        'private-path-name' => env('DFE_PRIVATE_PATH_NAME', ConsoleDefaults::PRIVATE_PATH_NAME),
        // relative to owner-private-path
        'snapshot-path-name' => env('DFE_SNAPSHOT_PATH_NAME', ConsoleDefaults::SNAPSHOT_PATH_NAME),
        /** "DFE_*_PATHS" variables can contain one or more, pipe-delimited names of directories to create */
        'public-paths' => explode('|', env('DFE_PUBLIC_PATHS', 'applications|.private')),
        'private-paths' => explode('|', env('DFE_PRIVATE_PATHS', '.cache|config|scripts|scripts.user|log')),
        'owner-private-paths' => explode('|', env('DFE_OWNER_PRIVATE_PATHS', ConsoleDefaults::SNAPSHOT_PATH_NAME)),
        //******************************************************************************
        //* Instance provisioning defaults
        //******************************************************************************
        'default-cluster-id' => env('DFE_DEFAULT_CLUSTER'),
        'default-db-server-id' => env('DFE_DEFAULT_DATABASE'),
        'default-guest-location' => env('DFE_DEFAULT_GUEST_LOCATION', GuestLocations::DFE_CLUSTER),
        'default-ram-size' => env('DFE_DEFAULT_RAM_SIZE', 1),
        'default-disk-size' => env('DFE_DEFAULT_DISK_SIZE', 8),
        //  Notification settings
        'email-subject-prefix' => env('DFE_EMAIL_SUBJECT_PREFIX', '[DFE]'),
        //  Instance defaults
        'default-dns-zone' => env('DFE_DEFAULT_DNS_ZONE'),
        'default-dns-domain' => env('DFE_DEFAULT_DNS_DOMAIN'),
        'default-domain' => env('DFE_DEFAULT_DOMAIN'),
        //@todo update image to 14.* LTS x64
        //  Ubuntu server 12.04.1 i386
        'default-vendor-image-id' => 4647,
        //	i386
        'default-vendor-image-flavor' => 0,
    ],
    //******************************************************************************
    //* Console API Keys
    //******************************************************************************
    'security' => [
        'console-api-url' => env('DFE_CONSOLE_API_URL'),
        /** This key needs to match the key configured in the dashboard */
        'console-api-key' => env('DFE_CONSOLE_API_KEY'),
        'console-api-client-id' => env('DFE_CONSOLE_API_CLIENT_ID'),
        'console-api-client-secret' => env('DFE_CONSOLE_API_CLIENT_SECRET'),
    ],
    //******************************************************************************
    //* Individual command settings
    //******************************************************************************
    'commands' => [
        //  Display header information
        'display-name' => 'DreamFactory Enterprise(tm) Console Manager',
        'display-version' => 'v1.0.x-alpha',
        'display-copyright' => 'Copyright (c) 2012-' . date('Y') . ', All Rights Reserved',
        'setup' => [
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
    'forbidden-names' => [
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