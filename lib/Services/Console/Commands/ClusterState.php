<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ClusterState extends Command
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @var string The console command name */
    protected $name = 'dfe:cluster-state';

    /**  @var string The console command description */
    protected $description = 'Prints out diagnostics information about a cluster';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['cluster-id', InputArgument::REQUIRED, 'The cluster id'],
        ];
    }

}
