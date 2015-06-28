<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Services\Jobs\ImportJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Import extends Command
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Command name
     */
    protected $name = 'dfe:import';
    /**
     * @type string Command description
     */
    protected $description = 'Imports a previously exported snapshot';

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
        return \Queue::push(
            new ImportJob(
                $this->argument('instance-id'),
                [
                    'snapshot' => $this->argument('snapshot'),
                ]
            )
        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['instance-id', InputArgument::REQUIRED, 'The name of the new instance'],
            ['snapshot', InputArgument::REQUIRED, 'The path of the snapshot file'],
        ];
    }
}
