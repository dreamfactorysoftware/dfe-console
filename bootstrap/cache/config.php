<?php return array (
  'view' => 
  array (
    'paths' => 
    array (
      0 => '/opt/dreamfactory/dfe/dfe-console/resources/views',
    ),
    'compiled' => '/opt/dreamfactory/dfe/dfe-console/storage/framework/views',
  ),
  'partners' => 
  array (
    'hs' => 
    array (
      'name' => 'HubSpot',
      'referrer' => 
      array (
        0 => 'hubspot.com',
      ),
      'commands' => 
      array (
        0 => 'register',
      ),
    ),
    'df' => 
    array (
      'name' => 'DreamFactory',
      'referrer' => 
      array (
        0 => 'dreamfactory.com',
      ),
      'commands' => 
      array (
        0 => 'register',
      ),
    ),
  ),
  'snapshot' => 
  array (
    'storage-path' => 'snapshots',
    'id-prefix' => 'dfe',
    'script' => 
    array (
      'location' => '/opt/dreamfactory/dfe/dfe-console/app/scripts/snapshot_mysql.sh',
      'user' => 'jablan',
    ),
    'metadata-type' => 'dfe.snapshot',
    'hash-link-base' => 'https://download.enterprise.dreamfactory.com',
    'days-to-keep' => 30,
    'soft-delete' => false,
    'trash-path' => '/data/trash',
    'templates' => 
    array (
      'snapshot-file-name' => '{snapshot-prefix}.snapshot.zip',
      'storage-file-name' => 'storage.zip',
      'db-file-name' => 'database.sql',
      'metadata-file-name' => 'snapshot.json',
      'metadata' => 
      array (
        'id' => '{id}',
        'type' => '{type}',
        'hash' => '{hash}',
        'link' => '{link}',
        'instance-id' => '{instance-id}',
        'cluster-id' => '{cluster-id}',
        'db-server-id' => '{db-server-id}',
        'app-server-id' => '{app-server-id}',
        'web-server-id' => '{web-server-id}',
        'storage-key' => '{storage-key}',
        'owner-id' => '{owner-id}',
        'owner-email-address' => '{owner-email-address}',
        'owner-storage-key' => '{owner-storage-key}',
        'storage-zipball' => '{contents-storage-zipball}',
        'storage-timestamp' => '{contents-storage-timestamp}',
        'db-dumpfile' => '{contents-db-dumpfile}',
        'db-timestamp' => '{contents-db-timestamp}',
      ),
      'imports' => 
      array (
      ),
      'exports' => 
      array (
      ),
    ),
  ),
  'queue' => 
  array (
    'default' => 'sync',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'job_t',
        'queue' => 'default',
        'expire' => 60,
        'database' => 'dfe-local',
      ),
    ),
    'failed' => 
    array (
      'database' => 'dfe-local',
      'table' => 'job_fail_t',
    ),
  ),
  'links' => 
  array (
    0 => 
    array (
      'name' => 'Welcome',
      'href' => '//www.dreamfactory.com/in_product_welcome.html',
      'attributes' => 
      array (
      ),
    ),
    1 => 
    array (
      'name' => 'Resources',
      'href' => '//www.dreamfactory.com/in_product_resources.html',
      'attributes' => 
      array (
      ),
    ),
    2 => 
    array (
      'name' => 'Download',
      'href' => '//www.dreamfactory.com/in_product_downloads.html',
      'attributes' => 
      array (
      ),
    ),
  ),
  'flysystem' => 
  array (
    'default' => 'local',
    'connections' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'path' => '/data/storage',
      ),
      'cluster-east-2' => 
      array (
        'driver' => 'local',
        'path' => '/data/storage',
      ),
      'mount-east-1' => 
      array (
        'driver' => 'local',
        'path' => '/data/storage',
      ),
      'dfe-mount-east-1' => 
      array (
        'driver' => 'local',
        'path' => '/data/storage',
      ),
      'mount-local-1' => 
      array (
        'driver' => 'local',
        'path' => '/data/storage',
      ),
    ),
    'cache' => 
    array (
      'foo' => 
      array (
        'driver' => 'illuminate',
        'connector' => NULL,
        'key' => 'foo',
      ),
      'bar' => 
      array (
        'driver' => 'illuminate',
        'connector' => 'redis',
        'key' => 'bar',
        'ttl' => 600,
      ),
      'adapter' => 
      array (
        'driver' => 'adapter',
        'adapter' => 'local',
        'file' => 'flysystem.json',
        'ttl' => 600,
      ),
    ),
  ),
  'dfe' => 
  array (
    'cluster-id' => 'cluster-east-2',
    'instance-prefix' => '',
    'signature-method' => 'sha256',
    'common' => 
    array (
      'display-name' => 'DreamFactory Enterprise&trade; Console',
      'display-version' => 'v1.0.x-alpha',
      'display-copyright' => '© DreamFactory Software, Inc. 2012-2015. All Rights Reserved.',
      'themes' => 
      array (
        'auth' => 'darkly',
        'page' => 'flatly',
      ),
    ),
    'provisioners' => 
    array (
      'default' => 'rave',
      'hosts' => 
      array (
        'rave' => 
        array (
          'instance' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\Provisioner',
          'storage' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\StorageProvisioner',
          'db' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\DatabaseProvisioner',
          'offerings' => 
          array (
            'instance-version' => 
            array (
              'name' => 'Version',
              'help-block' => 'If you wish, you may choose a different version of the DSP to provision.',
              'suggested' => '1.10.x-dev',
              'items' => 
              array (
                '1.10.x-dev' => 
                array (
                  'document-root' => '/var/www/_releases/dsp-core/1.9.x-dev/web',
                  'description' => 'DSP v1.10.x-dev',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'provisioning' => 
    array (
      'storage-root' => '/data/storage',
      'storage-zone-type' => 'static',
      'static-zone-name' => 'ec2.us-east-1a',
      'public-path-base' => '/',
      'private-path-name' => '.private',
      'snapshot-path-name' => 'snapshots',
      'public-paths' => 
      array (
        0 => 'applications',
        1 => '.private',
      ),
      'private-paths' => 
      array (
        0 => '.cache',
        1 => 'config',
        2 => 'scripts',
        3 => 'scripts.user',
      ),
      'owner-private-paths' => 
      array (
        0 => 'snapshots',
      ),
      'default-cluster-id' => 'cluster-east-2',
      'default-db-server-id' => 'dfe-db-east-1',
      'default-guest-location' => 'rave',
      'default-ram-size' => '1',
      'default-disk-size' => '8',
      'email-subject-prefix' => '[DFE]',
      'default-dns-zone' => 'enterprise',
      'default-dns-domain' => 'dreamfactory.com',
      'default-domain' => '.enterprise.dreamfactory.com',
      'default-vendor-image-id' => 4647,
      'default-vendor-image-flavor' => 0,
    ),
    'security' => 
    array (
      'console-api-url' => 'http://dfe-console.local/api/v1/ops',
      'console-api-key' => '%]3,]~&t,EOxL30[wKw3auju:[+L>eYEVWEP,@3n79Qy',
      'console-api-client-id' => 'acbab38ec7c7f9eeb97ec957b53857050d8b3b7b753b95ffb31e7161140049ea',
      'console-api-client-secret' => '97b61eb7ad89bb63b6c575a90ffb86f971a7f0914210f84dcc827cd54fac4f27',
    ),
    'commands' => 
    array (
      'display-name' => 'DreamFactory Enterprise(tm) Console Manager',
      'display-version' => 'v1.0.x-alpha',
      'display-copyright' => 'Copyright (c) 2012-2015, All Rights Reserved',
      'setup' => 
      array (
        'required-directories' => 
        array (
          0 => 'bootstrap/cache',
          1 => 'storage/framework/cache',
          2 => 'storage/framework/sessions',
          3 => 'storage/framework/views',
          4 => 'storage/logs',
        ),
      ),
    ),
    'forbidden-names' => 
    array (
      0 => 'dreamfactory',
      1 => 'dream',
      2 => 'factory',
      3 => 'developer',
      4 => 'wiki',
      5 => 'enterprise',
      6 => 'cloud',
      7 => 'www',
      8 => 'fabric',
      9 => 'api',
      10 => 'db',
      11 => 'database',
      12 => 'dsp',
      13 => 'dfe',
      14 => 'dfac',
      15 => 'df',
      16 => 'dfab',
      17 => 'dfdsp',
      18 => 'email',
      19 => 'rave',
      20 => 'console',
      21 => 'dashboard',
      22 => 'launchpad',
      23 => 'test',
      24 => 'performance_schema',
      25 => 'information_schema',
      26 => 'mysql',
      27 => 'postgresql',
      28 => 'oracle',
      29 => 'dfe_local',
      30 => 'feces',
      31 => 'fecal',
      32 => 'defecate',
      33 => 'urinate',
      34 => 'inseminate',
      35 => 'cum',
      36 => 'jizz',
      37 => 'semen',
      38 => 'shit',
      39 => 'piss',
      40 => 'fuck',
      41 => 'cunt',
      42 => 'cocksucker',
      43 => 'motherfucker',
      44 => 'tits',
    ),
  ),
  'database' => 
  array (
    'fetch' => 8,
    'default' => 'dfe-local',
    'migrations' => 'migration_t',
    'connections' => 
    array (
      'dfe-local' => 
      array (
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'dfe_local',
        'username' => 'dfe_user',
        'password' => 'dfe_user',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
      ),
    ),
    'redis' => 
    array (
      'cluster' => false,
      'default' => 
      array (
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
      ),
    ),
  ),
  'auth' => 
  array (
    'driver' => 'console',
    'model' => 'DreamFactory\\Enterprise\\Database\\Models\\ServiceUser',
    'table' => 'service_user_t',
    'password' => 
    array (
      'email' => 'dfe-common::emails.password',
      'table' => 'auth_reset_t',
      'expire' => 60,
    ),
    'open-registration' => false,
  ),
  'instance' => 
  array (
  ),
  'compile' => 
  array (
    'files' => 
    array (
      0 => '/opt/dreamfactory/dfe/dfe-console/app/Providers/AppServiceProvider.php',
      1 => '/opt/dreamfactory/dfe/dfe-console/app/Providers/BusServiceProvider.php',
      2 => '/opt/dreamfactory/dfe/dfe-console/app/Providers/ConfigServiceProvider.php',
      3 => '/opt/dreamfactory/dfe/dfe-console/app/Providers/EventServiceProvider.php',
      4 => '/opt/dreamfactory/dfe/dfe-console/app/Providers/RouteServiceProvider.php',
    ),
    'providers' => 
    array (
      0 => 'DreamFactory\\Enterprise\\Services\\Auditing\\AuditServiceProvider',
    ),
  ),
  'elk' => 
  array (
    'host' => 'lps-east-1.fabric.dreamfactory.com',
    'port' => 9200,
    'timeout' => 90,
    'strategy' => '\\Elastica\\Connection\\Strategy\\Simple',
  ),
  'services' => 
  array (
    'mailgun' => 
    array (
      'domain' => 'mg.dreamfactory.com',
      'secret' => 'key-662ecc1e5c1e0cfe779d86f9efe44517',
    ),
  ),
  'cache' => 
  array (
    'default' => 'file',
    'prefix' => 'dfe',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache_t',
        'connection' => 'dfe-local',
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => '/opt/dreamfactory/dfe/dfe-console/storage/framework/cache',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
    ),
  ),
  'mail' => 
  array (
    'driver' => 'mailgun',
    'host' => 'smtp.mailgun.org',
    'port' => '587',
    'from' => 
    array (
      'address' => 'no.reply@dreamfactory.com',
      'name' => 'DreamFactory',
    ),
    'encryption' => 'tls',
    'username' => 'postmaster@mg.dreamfactory.com',
    'password' => '167c38b93a425fdb9866762d9bda3587',
    'sendmail' => '/usr/sbin/sendmail -bs',
    'pretend' => false,
  ),
  'ide-helper' => 
  array (
    'filename' => '_ide_helper',
    'format' => 'php',
    'include_helpers' => false,
    'helper_files' => 
    array (
      0 => '/opt/dreamfactory/dfe/dfe-console/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
    ),
    'model_locations' => 
    array (
      0 => 'app',
    ),
    'extra' => 
    array (
      'Eloquent' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'Illuminate\\Database\\Query\\Builder',
      ),
      'Session' => 
      array (
        0 => 'Illuminate\\Session\\Store',
      ),
    ),
    'magic' => 
    array (
      'Log' => 
      array (
        'debug' => 'Monolog\\Logger::addDebug',
        'info' => 'Monolog\\Logger::addInfo',
        'notice' => 'Monolog\\Logger::addNotice',
        'warning' => 'Monolog\\Logger::addWarning',
        'error' => 'Monolog\\Logger::addError',
        'critical' => 'Monolog\\Logger::addCritical',
        'alert' => 'Monolog\\Logger::addAlert',
        'emergency' => 'Monolog\\Logger::addEmergency',
      ),
    ),
    'interfaces' => 
    array (
      '\\Illuminate\\Contracts\\Auth\\Authenticatable' => 'DreamFactory\\Enterprise\\Database\\Models\\ServiceUser',
    ),
    'custom_db_types' => 
    array (
    ),
  ),
  'app' => 
  array (
    'debug' => true,
    'url' => 'http://dfe-console.local/',
    'timezone' => 'America/New_York',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'key' => 'oB62b5Z1p6YtTRdmr9e0zU0z1acYSxOc',
    'cipher' => 'rijndael-128',
    'log' => 'single',
    'providers' => 
    array (
      0 => 'Illuminate\\Foundation\\Providers\\ArtisanServiceProvider',
      1 => 'Illuminate\\Auth\\AuthServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Routing\\ControllerServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      15 => 'Illuminate\\Queue\\QueueServiceProvider',
      16 => 'Illuminate\\Redis\\RedisServiceProvider',
      17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Illuminate\\Translation\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'DreamFactory\\Enterprise\\Console\\Providers\\AppServiceProvider',
      23 => 'DreamFactory\\Enterprise\\Console\\Providers\\BusServiceProvider',
      24 => 'DreamFactory\\Enterprise\\Console\\Providers\\ConfigServiceProvider',
      25 => 'DreamFactory\\Enterprise\\Console\\Providers\\EventServiceProvider',
      26 => 'DreamFactory\\Enterprise\\Console\\Providers\\RouteServiceProvider',
      27 => 'DreamFactory\\Enterprise\\Common\\Providers\\LibraryAssetsProvider',
      28 => 'DreamFactory\\Enterprise\\Common\\Providers\\Auth\\ConsoleAuthProvider',
      29 => 'DreamFactory\\Enterprise\\Common\\Providers\\PacketServiceProvider',
      30 => 'DreamFactory\\Enterprise\\Common\\Providers\\RouteHashingServiceProvider',
      31 => 'DreamFactory\\Enterprise\\Common\\Providers\\ScalpelServiceProvider',
      32 => 'DreamFactory\\Enterprise\\Services\\Auditing\\AuditServiceProvider',
      33 => 'DreamFactory\\Enterprise\\Services\\Providers\\InstanceServiceProvider',
      34 => 'DreamFactory\\Enterprise\\Services\\Providers\\InstanceStorageServiceProvider',
      35 => 'DreamFactory\\Enterprise\\Storage\\Providers\\MountServiceProvider',
      36 => 'DreamFactory\\Enterprise\\Services\\Providers\\ProvisioningServiceProvider',
      37 => 'DreamFactory\\Enterprise\\Services\\Providers\\SnapshotServiceProvider',
      38 => 'DreamFactory\\Enterprise\\Console\\Providers\\ElkServiceProvider',
      39 => 'Barryvdh\\LaravelIdeHelper\\IdeHelperServiceProvider',
      40 => 'GrahamCampbell\\Flysystem\\FlysystemServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Input' => 'Illuminate\\Support\\Facades\\Input',
      'Inspiring' => 'Illuminate\\Foundation\\Inspiring',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Redis' => 'Illuminate\\Support\\Facades\\Redis',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Audit' => 'DreamFactory\\Enterprise\\Services\\Auditing\\Audit',
      'Provision' => 'DreamFactory\\Enterprise\\Services\\Facades\\Provision',
      'Snapshot' => 'DreamFactory\\Enterprise\\Services\\Facades\\Snapshot',
      'InstanceManager' => 'DreamFactory\\Enterprise\\Services\\Facades\\InstanceManager',
      'InstanceStorage' => 'DreamFactory\\Enterprise\\Services\\Facades\\InstanceStorage',
      'Mounter' => 'DreamFactory\\Enterprise\\Services\\Facades\\Mounter',
      'Flysystem' => 'GrahamCampbell\\Flysystem\\Facades\\Flysystem',
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'cloud' => 's3',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => '/opt/dreamfactory/dfe/dfe-console/storage/app',
      ),
      'cluster-east-2' => 
      array (
        'driver' => 'local',
        'root' => '/data/storage',
      ),
      'mount-east-1' => 
      array (
        'driver' => 'local',
        'root' => '/data/storage',
      ),
      'dfe-mount-east-1' => 
      array (
        'driver' => 'local',
        'root' => '/data/storage',
      ),
      'mount-local-1' => 
      array (
        'driver' => 'local',
        'root' => '/data/storage',
      ),
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => '/opt/dreamfactory/dfe/dfe-console/storage/framework/sessions',
    'connection' => NULL,
    'table' => 'session_t',
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'dfe-console-session',
    'path' => '/',
    'domain' => NULL,
    'secure' => false,
  ),
);
