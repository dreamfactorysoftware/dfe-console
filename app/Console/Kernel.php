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
        'DreamFactory\\Enterprise\\Console\\Commands\\Setup',
        'DreamFactory\\Enterprise\\Console\\Commands\\Register',
        'DreamFactory\\Enterprise\\Console\\Commands\\Mount',
        'DreamFactory\\Enterprise\\Console\\Commands\\Server',
        'DreamFactory\\Enterprise\\Console\\Commands\\Cluster',
        //  Services
        'DreamFactory\\Enterprise\\Console\\Commands\\Manifest',
        'DreamFactory\\Enterprise\\Console\\Commands\\Provision',
        'DreamFactory\\Enterprise\\Console\\Commands\\Deprovision',
        'DreamFactory\\Enterprise\\Console\\Commands\\Import',
        'DreamFactory\\Enterprise\\Console\\Commands\\Export',
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
