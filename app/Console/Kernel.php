<?php namespace DreamFactory\Enterprise\Console\Console;

use DreamFactory\Enterprise\Common\Traits\CommonLogging;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use CommonLogging;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @var array The artisan commands provided by your application.
     */
    protected $commands = [
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Provision',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Deprovision',
        //        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\ClusterState',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Import',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Export',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Register',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Manifest',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Setup',
    ];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule( Schedule $schedule )
    {
    }
}
