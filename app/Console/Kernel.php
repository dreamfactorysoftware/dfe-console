<?php namespace DreamFactory\Enterprise\Console\Console;

use Illuminate\Console\Scheduling\Schedule;
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
    //* Members
    //******************************************************************************

    /**
     * @var array The artisan commands provided by your application.
     */
    protected $commands = [
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Provision',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Deprovision',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\ClusterState',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Import',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Export',
        'DreamFactory\\Enterprise\\Services\\Console\\Commands\\Register',
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
     * @param string $fromClass The class to replace
     * @param string $toClass   The replacement
     */
    protected function _replaceClass( $fromClass = null, $toClass = null )
    {
        $_straps = array_flip( $this->bootstrappers );
        $fromClass = $fromClass ?: static::CLASS_TO_REPLACE;

        if ( array_key_exists( $fromClass, $_straps ) )
        {
            $this->bootstrappers[$_straps[$fromClass]] = $toClass ?: static::REPLACEMENT_CLASS;
        }
    }
}
