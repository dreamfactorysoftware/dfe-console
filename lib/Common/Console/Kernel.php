<?php namespace DreamFactory\Enterprise\Common\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * The console kernel
 */
class Kernel extends ConsoleKernel
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule( Schedule $schedule )
    {
    }

}
