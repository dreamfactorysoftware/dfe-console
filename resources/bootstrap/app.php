<?php
/**
 * DreamFactory encapsulated application bootstrap
 */
use DreamFactory\Library\Utility\Disk;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

//  Use "getcwd()" instead of __DIR__ as it resolves symlinks, which we do not want
$_app = new Illuminate\Foundation\Application(getcwd() . '/../');

//  Bind interfaces
$_app->singleton('Illuminate\Contracts\Http\Kernel', 'DreamFactory\Http\Kernel');
$_app->singleton('Illuminate\Contracts\Console\Kernel', 'DreamFactory\Console\Kernel');
$_app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', 'DreamFactory\Exceptions\Handler');

//  Configure logging
$_app->configureMonologUsing(function (Logger $monolog) {
    switch (config('app.log', 'single')) {
        case 'syslog':
            $monolog->pushHandler(new SyslogHandler('dreamfactory'));

            return;

        case 'errorlog':
            $_handler = new ErrorLogHandler();
            break;

        case 'single':
        default:
            $_handler = new StreamHandler(Disk::path([
                env('DF_MANAGED_LOG_PATH', storage_path('logs')),
                env('DF_MANAGED_LOG_FILE', 'dreamfactory.log'),
            ]));
            break;
    }

    $_handler->setFormatter(new LineFormatter(null, null, true, true));
    $monolog->pushHandler($_handler);
});

return $_app;
