<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;

class Token extends ConsoleCommand implements SelfHandling
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:token';
    /** @inheritdoc */
    protected $description = 'Generates a token for an instance';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function fire()
    {
        parent::fire();

        try {
            $_instance = $this->_findInstance($this->argument('instance-id'));
            $this->writeln('token: ' . $_instance->generateToken());

            return true;
        } catch (ModelNotFoundException $_ex) {
            $this->error('Instance not found.');

            return false;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['instance-id', InputArgument::REQUIRED, 'The instance id'],
            ]);
    }

}
