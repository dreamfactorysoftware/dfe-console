<?php
use DreamFactory\Enterprise\Common\Enums\MailTemplates;

/**
 * DFE core services configuration
 */
return [
    //******************************************************************************
    //* Services to be auto-registered
    //******************************************************************************
    'auto-register' => [
        /** DreamFactory Common service providers */
        'scalpel'              => 'DreamFactory\\Enterprise\\Common\\Providers\\ScalpelServiceProvider',
        'route-hashing'        => 'DreamFactory\\Enterprise\\Common\\Providers\\RouteHashingServiceProvider',
        'packet'               => 'DreamFactory\\Enterprise\\Common\\Providers\\PacketServiceProvider',
        /** DreamFactory Services service providers */
        'dfe.instance-manager' => 'DreamFactory\\Enterprise\\Services\\Providers\\InstanceManagerProvider',
        'dfe.provisioning'     => 'DreamFactory\\Enterprise\\Services\\Provisioners\\DreamFactory\\ProvisioningServiceProvider',
        'dfe.snapshot'         => 'DreamFactory\\Enterprise\\Services\\Providers\\SnapshotServiceProvider',
        'dfe.elk'              => 'DreamFactory\\Enterprise\\Console\\Providers\\ElkServiceProvider',
    ],
    //******************************************************************************
    //* Provisioning service settings
    //******************************************************************************
    'provisioning'  => [
        //  Storage & storage layout options/settings
        'storage-zone-type'           => 'static',          //  Either "static" or "dynamic"
        'static-zone-name'            => 'ec2.us-east-1a',  //  The "static" storage zone
        'hosted-storage-base-path'    => '/data/storage',   //  absolute path to storage drive
        'local-storage-base-path'     => 'storage',         //  relative to installation
        'public-path-base'            => '/',               //  relative to storage path (hosted or non)
        'private-path-base'           => '.private',        //  relative to storage path (hosted or non)
        'public-paths'                => ['applications', 'plugins', '.private',],
        'private-paths'               => ['.cache', 'config', 'scripts', 'scripts.user',],
        //  Instance defaults
        'default-cluster-id'          => 2,                 //  Default to cluster 2
        //@todo update image to 14.* LTS x64
        'default-vendor-image-id'     => 4647,              //	Ubuntu server 12.04.1 i386
        'default-vendor-image-flavor' => 0,                 //	i386
        'default-dns-zone'            => env( 'DFE_DEFAULT_ZONE', 'cloud' ),
        'default-dns-domain'          => env( 'DFE_DEFAULT_DOMAIN', 'dreamfactory.com' ),
    ],
    //******************************************************************************
    //* Mailgun
    //******************************************************************************
    'mailgun'       => [
        'domain' => env( 'MAILGUN_DOMAIN' ),
        'secret' => env( 'MAILGUN_SECRET_KEY' ),
    ],
    //******************************************************************************
    //* Mail template service
    //******************************************************************************
    'mail-template' => [
        'web-url'               => 'http://cerberus.fabric.dreamfactory.com/',
        'public-url'            => 'http://cerberus.fabric.dreamfactory.com/',
        'support-email-address' => 'support@dreamfactory.com',
        'confirmation-url'      => 'http://cerberus.fabric.dreamfactory.com/app/confirmation/',
        'smtp-service'          => 'localhost',
        //.........................................................................
        //. Templates
        //.........................................................................
        'templates'             => [
            MailTemplates::WELCOME              => [
                'subject'  => 'Welcome to DreamFactory Developer Central!',
                'template' => 'welcome-confirmation.html',
            ],
            MailTemplates::PASSWORD_RESET       => [
                'subject'  => 'Recover your DreamFactory password',
                'template' => 'recover-password.html',
            ],
            MailTemplates::PASSWORD_CHANGED     => [
                'subject'  => 'Your Password Has Been Changed',
                'template' => 'password-changed.html',
            ],
            MailTemplates::NOTIFICATION         => [
                'subject'  => null,
                'template' => 'notification.html',
            ],
            MailTemplates::SYSTEM_NOTIFICATION  => [
                'subject'  => null,
                'template' => 'system-notification.html',
            ],
            MailTemplates::PROVISION_COMPLETE   => [
                'subject'  => 'Your DSP is ready!',
                'template' => 'provisioning-complete.html',
            ],
            MailTemplates::DEPROVISION_COMPLETE => [
                'subject'  => 'Your DSP was removed!',
                'template' => 'deprovisioning-complete.html',
            ],
        ],
    ],
    //******************************************************************************
    //* Snapshot Service Configuration
    //******************************************************************************
    'snapshot'      => [
        //  The relative path under the local private file system. Resolves to "/path/to/storage/.private/[storage-path]"
        'storage-path'   => 'snapshots',
        //  The prefix, if any, to place before the timestamp when building the snapshot file name
        'id-prefix'      => 'ess',
        //  Scripts used by the snapshot service are defined here
        'script'         => [
            //  Where the cluster MySQL snapshot script is located
            'location' => base_path() . '/app/scripts/snapshot_mysql.sh',
            //  Which user to impersonate when running script
            'user'     => env( 'OS_SCRIPT_USER', 'jablan' ),
        ],
        //  The value to place in the meta data's "type" field
        'metadata-type'  => 'dfe.snapshot',
        //  The base URL for linking to snapshots
        'hash-link-base' => 'https://download.cloud.dreamfactory.com',
        //  Templates used by the snapshot service
        'templates'      => [
            //  File name templates
            'snapshot-file-name' => '{{ $snapshot_prefix }}.snapshot.zip',
            'storage-file-name'  => '{{ $snapshot_prefix }}.storage.zip',
            'db-file-name'       => '{{ $snapshot_prefix }}.sql',
            'metadata-file-name' => 'snapshot.json',
            //  Metadata guts template
            'metadata'           => [
                'id'       => '{{ $id }}',
                'type'     => '{{ $type }}',
                'hash'     => '{{ $hash }}',
                'link'     => '{{ $link }}',
                'source'   => [
                    'cluster-id'  => '{{ $source_cluster_id }}',
                    'instance-id' => '{{ $source_instance_id }}',
                    'database-id' => '{{ $source_database_id }}',
                    'storage-key' => '{{ $source_storage_key }}',
                    'private-key' => '{{ $source_private_key }}',
                ],
                'contents' => [
                    'storage' => [
                        'zipball'   => '{{ $contents_storage_zipball }}',
                        'timestamp' => '{{ $contents_storage_timestamp }}',
                    ],
                    'db'      => [
                        'zipball'   => '{{ $contents_db_zipball }}',
                        'timestamp' => '{{ $contents_db_timestamp }}',
                    ],
                ],
                'imports'  => [],
                'exports'  => [],
            ],
        ],
    ],
];
