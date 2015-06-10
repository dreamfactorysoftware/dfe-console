<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Services\Commands\RegisterJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Register extends Command
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

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
        $_command = new RegisterJob(
            $this->argument('owner-id'),
            $this->argument('owner-type')
        );

        \Queue::push($_command);

        return $_command->getResult();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        static $_types = [];

        if (empty($_types)) {
            foreach (OwnerTypes::getDefinedConstants(true) as $_constant) {
                $_types[] = '"' . strtolower($_constant) . '"';
            }
        }

        return array_merge(
            parent::getArguments(),
            [
                [
                    'owner-id',
                    InputArgument::REQUIRED,
                    'The id of the owner of this key'
                ],
                [
                    'owner-type',
                    InputArgument::REQUIRED,
                    'One of the following owner types: ' . implode(', ', $_types)
                ]
            ]
        );
    }
}
