<?php
//******************************************************************************
//* DFE Console snapshot service configuration
//******************************************************************************
use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

return [
    //  The relative path under the local private file system. Resolves to "/path/to/storage/.private/[storage-path]"
    'storage-path'   => ConsoleDefaults::SNAPSHOT_PATH_NAME,
    //  Scripts used by the snapshot service are defined here
    'script'         => [
        //  Where the cluster MySQL snapshot script is located
        'location' => app_path('scripts/snapshot_mysql.sh'),
        //  Which user to impersonate when running script
        'user'     => env('DFE_SCRIPT_USER', 'dfadmin'),
    ],
    //  The base URL for linking to snapshots
    'hash-link-base' => 'https://download.enterprise.dreamfactory.com/snapshot',
    //  The number of days to keep snapshots before removing them from storage
    'days-to-keep'   => env('DFE_SNAPSHOT_DAYS_TO_KEEP', EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP),
    //  If true, files are moved to 'soft-delete-path' instead of being deleted
    'soft-delete'    => env('DFE_SNAPSHOT_SOFT_DELETE', EnterpriseDefaults::SNAPSHOT_SOFT_DELETE),
    //  Where to move files that have expired when 'soft-delete' is TRUE
    'trash-path'     => env('DFE_SNAPSHOT_TRASH_PATH', EnterpriseDefaults::DEFAULT_TRASH_PATH),
    //  Templates used by the snapshot service
    'templates'      => [
        /** File names:  The token "{id}" can be used in the names and will be replaced with snapshot id when used. */
        'snapshot-file-name' => '{id}.snapshot.zip',
        'metadata-file-name' => 'snapshot.json',
    ],
];
