<?php
return [
    //******************************************************************************
    //* Application Settings
    //******************************************************************************
    'debug'           => env('APP_DEBUG', false),
    'url'             => env('APP_URL', 'http://dfe-console.local'),
    'timezone'        => 'America/New_York',
    'locale'          => 'en',
    'fallback_locale' => 'en',
    'key'             => env('APP_KEY'),
    'cipher'          => 'AES-256-CBC',
    'log'             => 'single',
    'version'         => null,
    //******************************************************************************
    //* Autoloaded Providers
    //******************************************************************************
    'providers'       => [
        /** Laravel Framework Service Providers... */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        DreamFactory\Enterprise\Console\Providers\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /** Application Service Providers... */
        DreamFactory\Enterprise\Console\Providers\AppServiceProvider::class,
        DreamFactory\Enterprise\Console\Providers\EventServiceProvider::class,
        DreamFactory\Enterprise\Console\Providers\RouteServiceProvider::class,
        /** DreamFactory Common service providers */
        DreamFactory\Enterprise\Common\Providers\Auth\ConsoleAuthProvider::class,
        DreamFactory\Enterprise\Common\Providers\DataShaperServiceProvider::class,
        DreamFactory\Enterprise\Common\Providers\LibraryAssetsProvider::class,
        DreamFactory\Enterprise\Common\Providers\PacketServiceProvider::class,
        DreamFactory\Enterprise\Common\Providers\ScalpelServiceProvider::class,
        /** DreamFactory Storage service providers */
        DreamFactory\Enterprise\Storage\Providers\InstanceStorageServiceProvider::class,
        DreamFactory\Enterprise\Storage\Providers\MountServiceProvider::class,
        /** DreamFactory Services service providers */
        DreamFactory\Enterprise\Services\Providers\LicenseServerServiceProvider::class,
        //DreamFactory\Enterprise\Services\Providers\TelemetryServiceProvider::class,
        DreamFactory\Enterprise\Services\Providers\UsageServiceProvider::class,
        DreamFactory\Enterprise\Services\Providers\DeactivationServiceProvider::class,
        DreamFactory\Enterprise\Services\Auditing\AuditServiceProvider::class,
        DreamFactory\Enterprise\Services\Providers\InstanceServiceProvider::class,
        DreamFactory\Enterprise\Services\Providers\ProvisioningServiceProvider::class,
        DreamFactory\Enterprise\Services\Providers\RouteHashingServiceProvider::class,
        DreamFactory\Enterprise\Services\Providers\SnapshotServiceProvider::class,
        DreamFactory\Enterprise\Services\Providers\BlueprintServiceProvider::class,
        /** DreamFactory Partner Services Provider */
        DreamFactory\Enterprise\Partner\Providers\PartnerServiceProvider::class,
        /** DreamFactory Instance API Services Provider */
        DreamFactory\Enterprise\Instance\Ops\Providers\InstanceApiClientServiceProvider::class,
        /** DreamFactory Enterprise Console Operations API Services Provider */
        DreamFactory\Enterprise\Console\Ops\Providers\OpsClientServiceProvider::class,
        /** 3rd-party Service Providers */
        Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
        GrahamCampbell\Flysystem\FlysystemServiceProvider::class,
    ],
    //******************************************************************************
    //* Aliases
    //******************************************************************************
    'aliases'         => [
        'App'             => Illuminate\Support\Facades\App::class,
        'Artisan'         => Illuminate\Support\Facades\Artisan::class,
        'Auth'            => Illuminate\Support\Facades\Auth::class,
        'Blade'           => Illuminate\Support\Facades\Blade::class,
        'Bus'             => Illuminate\Support\Facades\Bus::class,
        'Cache'           => Illuminate\Support\Facades\Cache::class,
        'Config'          => Illuminate\Support\Facades\Config::class,
        'Cookie'          => Illuminate\Support\Facades\Cookie::class,
        'Crypt'           => Illuminate\Support\Facades\Crypt::class,
        'DB'              => Illuminate\Support\Facades\DB::class,
        'Eloquent'        => Illuminate\Database\Eloquent\Model::class,
        'Event'           => Illuminate\Support\Facades\Event::class,
        'File'            => Illuminate\Support\Facades\File::class,
        'Gate'            => Illuminate\Support\Facades\Gate::class,
        'Hash'            => Illuminate\Support\Facades\Hash::class,
        'Input'           => Illuminate\Support\Facades\Input::class,
        'Inspiring'       => Illuminate\Foundation\Inspiring::class,
        'Lang'            => Illuminate\Support\Facades\Lang::class,
        'Log'             => Illuminate\Support\Facades\Log::class,
        'Mail'            => Illuminate\Support\Facades\Mail::class,
        'Password'        => Illuminate\Support\Facades\Password::class,
        'Queue'           => Illuminate\Support\Facades\Queue::class,
        'Redirect'        => Illuminate\Support\Facades\Redirect::class,
        'Redis'           => Illuminate\Support\Facades\Redis::class,
        'Request'         => Illuminate\Support\Facades\Request::class,
        'Response'        => Illuminate\Support\Facades\Response::class,
        'Route'           => Illuminate\Support\Facades\Route::class,
        'Schema'          => Illuminate\Support\Facades\Schema::class,
        'Session'         => Illuminate\Support\Facades\Session::class,
        'Storage'         => Illuminate\Support\Facades\Storage::class,
        'URL'             => Illuminate\Support\Facades\URL::class,
        'Validator'       => Illuminate\Support\Facades\Validator::class,
        'View'            => Illuminate\Support\Facades\View::class,
        /** DreamFactory Aliases */
        'Audit'           => DreamFactory\Enterprise\Services\Auditing\Audit::class,
        'InstanceManager' => DreamFactory\Enterprise\Services\Facades\InstanceManager::class,
        'InstanceStorage' => DreamFactory\Enterprise\Storage\Facades\InstanceStorage::class,
        'Mounter'         => DreamFactory\Enterprise\Storage\Facades\Mounter::class,
        'Partner'         => DreamFactory\Enterprise\Partner\Facades\Partner::class,
        'Provision'       => DreamFactory\Enterprise\Services\Facades\Provision::class,
        'Snapshot'        => DreamFactory\Enterprise\Services\Facades\Snapshot::class,
        /** Third-party Aliases */
        'Flysystem'       => GrahamCampbell\Flysystem\Facades\Flysystem::class,
    ],
];
