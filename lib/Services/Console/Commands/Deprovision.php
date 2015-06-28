<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Console\Input\InputArgument;

class Deprovision extends Command
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string */
    const COMMAND_QUEUE = 'deprovision';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Command name
     */
    protected $name = 'dfe:deprovision';
    /**
     * @type string Command description
     */
    protected $description = 'Deprovisions, or shuts down, a running DSP';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function fire()
    {
        return
            \Queue::push(new DeprovisionJob($this->argument('instance-id')));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['instance-id', InputArgument::REQUIRED, 'The name of the instance to deprovision'],
        ];
    }

}
