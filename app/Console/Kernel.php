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
        'DreamFactory\Enterprise\Console\Console\Commands\Update',
        'DreamFactory\Enterprise\Console\Console\Commands\Setup',
        'DreamFactory\Enterprise\Console\Console\Commands\Register',
        'DreamFactory\Enterprise\Console\Console\Commands\Mount',
        'DreamFactory\Enterprise\Console\Console\Commands\Server',
        'DreamFactory\Enterprise\Console\Console\Commands\Cluster',
        'DreamFactory\Enterprise\Console\Console\Commands\Token',
        'DreamFactory\Enterprise\Console\Console\Commands\Metrics',
        'DreamFactory\Enterprise\Console\Console\Commands\Blueprint',
        //  Services
        'DreamFactory\Enterprise\Console\Console\Commands\Manifest',
        'DreamFactory\Enterprise\Console\Console\Commands\Provision',
        'DreamFactory\Enterprise\Console\Console\Commands\Deprovision',
        'DreamFactory\Enterprise\Console\Console\Commands\Import',
        'DreamFactory\Enterprise\Console\Console\Commands\Export',
    ];

    protected function schedule(Schedule $schedule)
    {
        parent::schedule($schedule);
    }

}
