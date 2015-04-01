<?php
use DreamFactory\Enterprise\Common\Enums\MailTemplates;

/**
 * DFE core services configuration
 */
return [
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
        'id-prefix'      => 'dfe',
        //  Scripts used by the snapshot service are defined here
        'script'         => [
            //  Where the cluster MySQL snapshot script is located
            'location' => app_path( 'scripts/snapshot_mysql.sh' ),
            //  Which user to impersonate when running script
            'user'     => env( 'DFE_SCRIPT_USER', 'dfadmin' ),
        ],
        //  The value to place in the meta data's "type" field
        'metadata-type'  => 'dfe.snapshot',
        //  The base URL for linking to snapshots
        'hash-link-base' => 'https://download.cloud.dreamfactory.com',
        //  Templates used by the snapshot service
        'templates'      => [
            //  File name templates
            'snapshot-file-name' => '{snapshot-prefix}.snapshot.zip',
            'storage-file-name'  => '{snapshot-prefix}.storage.zip',
            'db-file-name'       => '{snapshot-prefix}.sql',
            'metadata-file-name' => 'snapshot.json',
            //  Metadata guts template
            'metadata'           => [
                'id'       => '{id}',
                'type'     => '{type}',
                'hash'     => '{hash}',
                'link'     => '{link}',
                'source'   => [
                    'instance-id'         => '{instance-id}',
                    'cluster-id'          => '{cluster-id}',
                    'db-server-id'        => '{db-server-id}',
                    'app-server-id'       => '{app-server-id}',
                    'web-server-id'       => '{web-server-id}',
                    'storage-key'         => '{storage-key}',
                    'owner-storage-key'   => '{owner-storage-key}',
                    'owner-id'            => '{owner-id}',
                    'owner-email-address' => '{owner-email-address}',

                ],
                'contents' => [
                    'storage' => [
                        'zipball'   => '{contents-storage-zipball}',
                        'timestamp' => '{contents-storage-timestamp}',
                    ],
                    'db'      => [
                        'zipball'   => '{contents-db-zipball}',
                        'timestamp' => '{contents-db-timestamp}',
                    ],
                ],
                'imports'  => [],
                'exports'  => [],
            ],
        ],
    ],
];
