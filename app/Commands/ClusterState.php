<?php namespace DreamFactory\Enterprise\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;

class ClusterState extends ConsoleCommand
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
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['cluster-id', InputArgument::REQUIRED, 'The cluster id'],
            ]);
    }

}
