<?php
//******************************************************************************
//* Snapshot Service Configuration
//******************************************************************************
return [
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
];
