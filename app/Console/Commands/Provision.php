<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Services\Commands\Provision as ServicesProvision;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Provision extends Command
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
     * Handle the command
     *
     * @return mixed
     */
    public function fire()
    {
        $_request =
            new ServicesProvision(
                $this->argument( 'instance-id' ),
                [
                    'guest-location' => $this->option( 'guest-location' ),
                    'owner-id'       => $this->argument( 'owner-id' )
                ]
            );

        Queue::pushOn( 'provision', $_request );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['owner-id', InputArgument::REQUIRED, 'The id of the owner of the new instance'],
            ['instance-id', InputArgument::REQUIRED, 'The name of the new instance'],
            ['guest-location', InputArgument::OPTIONAL, 'The location of the new instance. Values can be 1 for AWS, 2 for DFE, or 3 for Azure.', 2],
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
            ['guest-location', 'g', InputOption::VALUE_OPTIONAL, 'The location of the new instance. Values: 1 = AWS, 2 = DFE, or 3 = Azure.', 2],
            ['cluster-id', 'c', InputOption::VALUE_OPTIONAL, 'The cluster-id where this instance should be placed.'],
            ['restart', 'r', InputOption::VALUE_NONE, 'If specified, an existing stopped instance will be restarted.'],
            ['trial', 't', InputOption::VALUE_NONE, 'If specified, sets the trial flag to TRUE on the provisioned instance.'],
            ['tag', 'a', InputOption::VALUE_OPTIONAL, 'The key to use for retrieving this instance from the manager. Defaults to the instance name.']
        ];
    }

}
