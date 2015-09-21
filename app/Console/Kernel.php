<?php namespace DreamFactory\Enterprise\Console\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @var array The artisan commands provided by your application.
     */
    protected $commands = [
        //  Core
        DreamFactory\Enterprise\Console\Console\Commands\Update::class,
        DreamFactory\Enterprise\Console\Console\Commands\Setup::class,
        DreamFactory\Enterprise\Console\Console\Commands\Register::class,
        DreamFactory\Enterprise\Console\Console\Commands\Mount::class,
        DreamFactory\Enterprise\Console\Console\Commands\Server::class,
        DreamFactory\Enterprise\Console\Console\Commands\Cluster::class,
        DreamFactory\Enterprise\Console\Console\Commands\Token::class,
        DreamFactory\Enterprise\Console\Console\Commands\Metrics::class,
        //  Services
        DreamFactory\Enterprise\Console\Console\Commands\Manifest::class,
        DreamFactory\Enterprise\Console\Console\Commands\Provision::class,
        DreamFactory\Enterprise\Console\Console\Commands\Deprovision::class,
        DreamFactory\Enterprise\Console\Console\Commands\Import::class,
        DreamFactory\Enterprise\Console\Console\Commands\Export::class,
    ];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
    }
}
