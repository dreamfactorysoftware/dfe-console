<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Provisioners\PortableServiceRequest;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;
use Illuminate\Contracts\Bus\SelfHandling;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Import extends ConsoleCommand implements SelfHandling
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
        parent::fire();

        $_request = PortableServiceRequest::makeImport($this->argument('instance-id'),
            $this->argument('snapshot'),
            array_merge(['owner-id' => $this->argument('owner-id'),], $this->getOptions()));

        $_job = new ImportJob($_request);
        $_result = $this->dispatch($_job);

        return [$_result, $_job];
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
                ['owner-id', InputArgument::REQUIRED, 'The id of the owner of the new instance'],
                ['instance-id', InputArgument::REQUIRED, 'The name of the new instance'],
                ['snapshot', InputArgument::REQUIRED, 'The path of the snapshot file'],
                [
                    'guest-location',
                    InputArgument::OPTIONAL,
                    'The location of the new instance',
                    config('provisioning.default-guest-location'),
                ],
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
                [
                    'owner-type',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'The owner-id of the new instance',
                ],
            ]);
    }
}
