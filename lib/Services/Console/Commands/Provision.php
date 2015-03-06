<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
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
        return Queue::push(
            new ProvisionJob(
                $this->argument( 'instance-id' ),
                [
                    'guest-location' => $this->argument( 'guest-location' ),
                    'owner-id'       => $this->argument( 'owner-id' ),
                    'cluster-id'     => $this->option( 'cluster-id' ),
                    'restart'        => $this->option( 'restart' ),
                    'trial'          => $this->option( 'trial' ),
                    'tag'            => $this->option( 'tag' ),
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
            ['owner-id', InputArgument::REQUIRED, 'The id of the owner of the new instance'],
            ['instance-id', InputArgument::REQUIRED, 'The name of the new instance'],
            [
                'guest-location',
                InputArgument::OPTIONAL,
                'The location of the new instance. Values: 1 = DFE, 2 = AWS, or 3 = Azure.',
                Config::get( 'dfe.provisioning.default-guest-location' )
            ],
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
            [
                'cluster-id',
                'c',
                InputOption::VALUE_OPTIONAL,
                'The cluster-id where this instance should be placed.',
                Config::get( 'dfe.provisioning.default-cluster-id' )
            ],
            ['restart', 'r', InputOption::VALUE_NONE, 'If specified, an existing stopped instance will be restarted.'],
            ['trial', 't', InputOption::VALUE_NONE, 'If specified, sets the trial flag to TRUE on the provisioned instance.'],
            ['tag', 'a', InputOption::VALUE_OPTIONAL, 'The key to use for retrieving this instance from the manager. Defaults to the instance name.']
        ];
    }

}
