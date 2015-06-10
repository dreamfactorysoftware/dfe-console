<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Services\Commands\ExportJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Export extends Command
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Command name
     */
    protected $name = 'dfe:export';
    /**
     * @type string Command description
     */
    protected $description = 'Creates an importable snapshot of an instance';

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
            new ExportJob(
                $this->argument('instance-id'),
                [
                    'destination' => $this->option('destination'),
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
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['destination', 'd', InputOption::VALUE_OPTIONAL, 'The path to where you would like the export placed.']
        ];
    }

}
