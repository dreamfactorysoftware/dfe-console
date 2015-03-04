<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Services\EntityLookup;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ClusterState extends Command
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @var string The console command name */
    protected $name = 'dfe:cluster-state';

    /**  @var string The console command description */
    protected $description = 'Command description.';

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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}
