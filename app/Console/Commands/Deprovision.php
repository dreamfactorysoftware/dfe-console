<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Deprovision extends ConsoleCommand
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
    protected $description = 'Deprovisions, or shuts down, a running instance';

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
        parent::fire();

        return \Queue::push(new DeprovisionJob($this->argument('instance-id'),
            ['cluster-id' => $this->option('cluster-id'),]));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
                ['instance-id', InputArgument::REQUIRED, 'The instance to deprovision'],
            ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
                [
                    'cluster-id',
                    'c',
                    InputOption::VALUE_OPTIONAL,
                    'The cluster containing the instance',
                    config('provisioning.default-cluster-id'),
                ],
            ]);
    }
}
