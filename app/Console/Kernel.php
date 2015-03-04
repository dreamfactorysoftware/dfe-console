<?php
namespace DreamFactory\Enterprise\Console\Console;

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
        'DreamFactory\\Enterprise\\Console\\Console\\Commands\\ClusterState',
        'DreamFactory\\Enterprise\\Console\\Console\\Commands\\Deprovision',
        'DreamFactory\\Enterprise\\Console\\Console\\Commands\\Provision',
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
