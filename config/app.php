<?php
return [
    //******************************************************************************
    //* Application Settings
    //******************************************************************************
    'debug'           => env( 'APP_DEBUG' ),
    'url'             => env( 'APP_URL', 'http://localhost' ),
    'timezone'        => 'America/New_York',
    'locale'          => 'en',
    'fallback_locale' => 'en',
    'key'             => env( 'APP_KEY', 'ngv?hS"qNs5:~Gn%]R(_NCRS#1{l?s@/' ),
    'cipher'          => MCRYPT_RIJNDAEL_128,
    'log'             => 'daily',
    //******************************************************************************
    //* Autoloaded Providers
    //******************************************************************************
    'providers'       => [
        /** Laravel Framework Service Providers... */
        'Illuminate\\Foundation\\Providers\\ArtisanServiceProvider',
        'Illuminate\\Auth\\AuthServiceProvider',
        'Illuminate\\Bus\\BusServiceProvider',
        'Illuminate\\Cache\\CacheServiceProvider',
        'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
        'Illuminate\\Routing\\ControllerServiceProvider',
        'Illuminate\\Cookie\\CookieServiceProvider',
        'Illuminate\\Database\\DatabaseServiceProvider',
        'Illuminate\\Encryption\\EncryptionServiceProvider',
        'Illuminate\\Filesystem\\FilesystemServiceProvider',
        'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
        'Illuminate\\Hashing\\HashServiceProvider',
        'Illuminate\\Mail\\MailServiceProvider',
        'Illuminate\\Pagination\\PaginationServiceProvider',
        'Illuminate\\Pipeline\\PipelineServiceProvider',
        'Illuminate\\Queue\\QueueServiceProvider',
        'Illuminate\\Redis\\RedisServiceProvider',
        'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
        'Illuminate\\Session\\SessionServiceProvider',
        'Illuminate\\Translation\\TranslationServiceProvider',
        'Illuminate\\Validation\\ValidationServiceProvider',
        'Illuminate\\View\\ViewServiceProvider',
        /** Application Service Providers... */
        'DreamFactory\\Enterprise\\Console\\Providers\\AppServiceProvider',
        'DreamFactory\\Enterprise\\Console\\Providers\\BusServiceProvider',
        'DreamFactory\\Enterprise\\Console\\Providers\\ConfigServiceProvider',
        'DreamFactory\\Enterprise\\Console\\Providers\\EventServiceProvider',
        'DreamFactory\\Enterprise\\Console\\Providers\\RouteServiceProvider',
        /** DreamFactory Common service providers */
        'DreamFactory\\Library\\Fabric\\Auditing\\Services\\AuditServiceProvider',
        'DreamFactory\\Enterprise\\Common\\Providers\\LibraryAssetsProvider',
        'DreamFactory\\Enterprise\\Common\\Providers\\Auth\\ConsoleAuthProvider',
        'DreamFactory\\Enterprise\\Common\\Providers\\PacketServiceProvider',
        'DreamFactory\\Enterprise\\Common\\Providers\\RouteHashingServiceProvider',
        'DreamFactory\\Enterprise\\Common\\Providers\\ScalpelServiceProvider',
        /** DreamFactory Services service providers */
        'DreamFactory\\Enterprise\\Services\\Providers\\RaveDatabaseServiceProvider',
        'DreamFactory\\Enterprise\\Services\\Providers\\InstanceManagerProvider',
        'DreamFactory\\Enterprise\\Services\\Providers\\ProvisioningServiceProvider',
        'DreamFactory\\Enterprise\\Services\\Providers\\SnapshotServiceProvider',
        /** DreamFactory Console (local app) service providers */
        'DreamFactory\\Enterprise\\Console\\Providers\\ElkServiceProvider',
        /** 3rd-party Service Providers */
        'Barryvdh\\LaravelIdeHelper\\IdeHelperServiceProvider',
    ],
    //******************************************************************************
    //* Aliases
    //******************************************************************************
    'aliases'         => [
        'App'       => 'Illuminate\\Support\\Facades\\App',
        'Artisan'   => 'Illuminate\\Support\\Facades\\Artisan',
        'Auth'      => 'Illuminate\\Support\\Facades\\Auth',
        'Blade'     => 'Illuminate\\Support\\Facades\\Blade',
        'Bus'       => 'Illuminate\\Support\\Facades\\Bus',
        'Cache'     => 'Illuminate\\Support\\Facades\\Cache',
        'Config'    => 'Illuminate\\Support\\Facades\\Config',
        'Cookie'    => 'Illuminate\\Support\\Facades\\Cookie',
        'Crypt'     => 'Illuminate\\Support\\Facades\\Crypt',
        'DB'        => 'Illuminate\\Support\\Facades\\DB',
        'Eloquent'  => 'Illuminate\\Database\\Eloquent\\Model',
        'Event'     => 'Illuminate\\Support\\Facades\\Event',
        'File'      => 'Illuminate\\Support\\Facades\\File',
        'Hash'      => 'Illuminate\\Support\\Facades\\Hash',
        'Input'     => 'Illuminate\\Support\\Facades\\Input',
        'Inspiring' => 'Illuminate\\Foundation\\Inspiring',
        'Lang'      => 'Illuminate\\Support\\Facades\\Lang',
        'Log'       => 'Illuminate\\Support\\Facades\\Log',
        'Mail'      => 'Illuminate\\Support\\Facades\\Mail',
        'Password'  => 'Illuminate\\Support\\Facades\\Password',
        'Queue'     => 'Illuminate\\Support\\Facades\\Queue',
        'Redirect'  => 'Illuminate\\Support\\Facades\\Redirect',
        'Redis'     => 'Illuminate\\Support\\Facades\\Redis',
        'Request'   => 'Illuminate\\Support\\Facades\\Request',
        'Response'  => 'Illuminate\\Support\\Facades\\Response',
        'Route'     => 'Illuminate\\Support\\Facades\\Route',
        'Schema'    => 'Illuminate\\Support\\Facades\\Schema',
        'Session'   => 'Illuminate\\Support\\Facades\\Session',
        'Storage'   => 'Illuminate\\Support\\Facades\\Storage',
        'URL'       => 'Illuminate\\Support\\Facades\\URL',
        'Validator' => 'Illuminate\\Support\\Facades\\Validator',
        'View'      => 'Illuminate\\Support\\Facades\\View',
        /** DreamFactory Aliases */
        'Provision' => 'DreamFactory\\Enterprise\\Services\\Facades\\Provision',
    ],

];
