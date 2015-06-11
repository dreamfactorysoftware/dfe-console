<?php
//******************************************************************************
//* Snapshot Service Configuration
//******************************************************************************
use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;

return [
    //  The relative path under the local private file system. Resolves to "/path/to/storage/.private/[storage-path]"
    'storage-path'   => 'snapshots',
    //  The prefix, if any, to place before the timestamp when building the snapshot file name
    'id-prefix'      => 'dfe',
    //  Scripts used by the snapshot service are defined here
    'script'         => [
        //  Where the cluster MySQL snapshot script is located
        'location' => app_path('scripts/snapshot_mysql.sh'),
        //  Which user to impersonate when running script
        'user'     => env('DFE_SCRIPT_USER', 'dfadmin'),
    ],
    //  The value to place in the meta data's "type" field
    'metadata-type'  => 'dfe.snapshot',
    //  The base URL for linking to snapshots
    'hash-link-base' => 'https://download.enterprise.dreamfactory.com',
    //  The number of days to keep snapshots before removing them from storage
    'days-to-keep'   => env('DFE_SNAPSHOT_DAYS_TO_KEEP', EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP),
    //  If true, files are moved to 'soft-delete-path' instead of being deleted
    'soft-delete'    => env('DFE_SNAPSHOT_SOFT_DELETE', EnterpriseDefaults::SNAPSHOT_SOFT_DELETE),
    //  Where to move files that have expired when 'soft-delete' is TRUE
    'trash-path'     => env('DFE_SNAPSHOT_TRASH_PATH', EnterpriseDefaults::DEFAULT_TRASH_PATH),
    //  Templates used by the snapshot service
    'templates'      => [
        //  File name templates
        'snapshot-file-name' => '{snapshot-prefix}.snapshot.zip',
        'storage-file-name'  => 'storage.zip',
        'db-file-name'       => 'database.sql',
        'metadata-file-name' => 'snapshot.json',
        //  Metadata guts template
        'metadata'           => [
            'id'                  => '{id}',
            'type'                => '{type}',
            'hash'                => '{hash}',
            'link'                => '{link}',
            'instance-id'         => '{instance-id}',
            'cluster-id'          => '{cluster-id}',
            'db-server-id'        => '{db-server-id}',
            'app-server-id'       => '{app-server-id}',
            'web-server-id'       => '{web-server-id}',
            'storage-key'         => '{storage-key}',
            'owner-id'            => '{owner-id}',
            'owner-email-address' => '{owner-email-address}',
            'owner-storage-key'   => '{owner-storage-key}',
            'storage-zipball'     => '{contents-storage-zipball}',
            'storage-timestamp'   => '{contents-storage-timestamp}',
            'db-dumpfile'         => '{contents-db-dumpfile}',
            'db-timestamp'        => '{contents-db-timestamp}',
        ],
        'imports'            => [],
        'exports'            => [],
    ],
];
