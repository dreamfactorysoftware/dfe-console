<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Services\Commands\RegisterJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Register extends Command
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @var string The console command name */
    protected $name = 'dfe:register';
    /**  @var string The console command description */
    protected $description = 'Creates a key pair used to communicate with the DFE Console';

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
            new RegisterJob(
                $this->argument( 'owner-id' ),
                $this->argument( 'entity-type' ),
                $this->argument( 'owner-type' )
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
        return array_merge(
            parent::getArguments(),
            [
                [
                    'owner-id',
                    InputArgument::REQUIRED,
                    'The id of the owner of this key'
                ],
                [
                    'entity-type',
                    InputArgument::OPTIONAL,
                    'The type of registrant. Must be one of the following: "application", "service", "user", "instance", "server", or "cluster".',
                    'user',
                ],
                [
                    'owner-type',
                    InputArgument::OPTIONAL,
                    'The type of owner. Must be an integer.'
                ],
            ]
        );
    }
}
