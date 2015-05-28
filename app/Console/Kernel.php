<?php namespace DreamFactory\Enterprise\Console\Console;

use DreamFactory\Enterprise\Common\Traits\CustomLogPath;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const CLASS_TO_REPLACE = 'Illuminate\Foundation\Bootstrap\ConfigureLogging';
    /**
     * @type string
     */
    const REPLACEMENT_CLASS = 'DreamFactory\Enterprise\Common\Bootstrap\CommonLoggingConfiguration';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use CustomLogPath;

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

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Contracts\Events\Dispatcher      $events
     */
    public function __construct( Application $app, Dispatcher $events )
    {
        $this->_replaceLoggingConfigurationClass( static::CLASS_TO_REPLACE, static::REPLACEMENT_CLASS );

        parent::__construct( $app, $events );
    }
}
