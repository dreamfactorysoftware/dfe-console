<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Common\Provisioners\PortableServiceRequest;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Import extends Command
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Command name
     */
    protected $name = 'dfe:import';
    /**
     * @type string Command description
     */
    protected $description = 'Imports a previously exported snapshot';

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
        $_request = new PortableServiceRequest($this->argument('instance-id'), $this->argument('snapshot-id'));

        return \Queue::push(new ImportJob($_request,
            $this->argument('instance-id'),
            null,
            $this->argument('snapshot')));
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
                ['instance-id', InputArgument::REQUIRED, 'The name of the new instance'],
                ['snapshot', InputArgument::REQUIRED, 'The path of the snapshot file'],
            ]);
    }
}
