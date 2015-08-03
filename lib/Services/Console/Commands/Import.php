<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Common\Provisioners\PortableServiceRequest;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
    protected $description = 'Import a portable instance export';

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
        $_request = PortableServiceRequest::makeImport($this->argument('instance-id'),
            $this->argument('snapshot'),
            $this->getOptions());

        \Queue::push($_job = new ImportJob($_request));

        return $_job->getResult();
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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getArguments(),
            [
                [
                    'cluster-id',
                    'c',
                    InputOption::VALUE_OPTIONAL,
                    'The cluster where this instance is to be placed.',
                    config('provisioning.default-cluster-id'),
                ],
                [
                    'snapshot-id',
                    'i',
                    InputOption::VALUE_NONE,
                    'If specified, the "snapshot" value is a snapshot-id not a path',
                ],
            ]);
    }
}
