<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ArtisanCommand;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class Provision extends ArtisanCommand implements ShouldBeQueued
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Command name
     */
    protected $name = 'dfe:provision';
    /**
     * @type string Command description
     */
    protected $description = 'Provisions a new DSP';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Run the command
     */
    public function handle()
    {

    }
}
