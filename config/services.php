<?php
use DreamFactory\Enterprise\Common\Enums\MailTemplates;
use DreamFactory\Enterprise\Common\Providers\PacketServiceProvider;
use DreamFactory\Enterprise\Common\Providers\RouteHashingServiceProvider;
use DreamFactory\Enterprise\Common\Providers\ScalpelServiceProvider;
use DreamFactory\Enterprise\Console\Providers\ElkServiceProvider;
use DreamFactory\Enterprise\Services\Providers\InstanceManagerProvider;
use DreamFactory\Enterprise\Services\Providers\ProvisioningServiceProvider;
use DreamFactory\Enterprise\Services\Providers\RaveDatabaseServiceProvider;
use DreamFactory\Enterprise\Services\Providers\SnapshotServiceProvider;

/**
 * DFE core services configuration
 */
return [
    //******************************************************************************
    //* Services to be auto-registered
    //******************************************************************************
    'auto-register' => [
        /** DreamFactory Console (local app) service providers */
        ElkServiceProvider::IOC_NAME          => 'DreamFactory\\Enterprise\\Console\\Providers\\ElkServiceProvider',
        /** DreamFactory Common service providers */
        ScalpelServiceProvider::IOC_NAME      => 'DreamFactory\\Enterprise\\Common\\Providers\\ScalpelServiceProvider',
        RouteHashingServiceProvider::IOC_NAME => 'DreamFactory\\Enterprise\\Common\\Providers\\RouteHashingServiceProvider',
        PacketServiceProvider::IOC_NAME       => 'DreamFactory\\Enterprise\\Common\\Providers\\PacketServiceProvider',
        /** DreamFactory Services service providers */
        InstanceManagerProvider::IOC_NAME     => 'DreamFactory\\Enterprise\\Services\\Providers\\InstanceManagerProvider',
        ProvisioningServiceProvider::IOC_NAME => 'DreamFactory\\Enterprise\\Services\\Providers\\ProvisioningServiceProvider',
        SnapshotServiceProvider::IOC_NAME     => 'DreamFactory\\Enterprise\\Services\\Providers\\SnapshotServiceProvider',
        RaveDatabaseServiceProvider::IOC_NAME => 'DreamFactory\\Enterprise\\Services\\Providers\\RaveDatabaseServiceProvider',
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
