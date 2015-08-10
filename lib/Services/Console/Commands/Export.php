<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Common\Provisioners\PortableServiceRequest;
use DreamFactory\Enterprise\Services\Jobs\ExportJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Export extends Command
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Command name
     */
    protected $name = 'dfe:export';
    /**
     * @type string Command description
     */
    protected $description = 'Create a portable instance export';

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
        $_request = PortableServiceRequest::makeExport($this->argument('instance-id'),
            $this->option('destination'));

        $_job = new ExportJob($_request);

        \Queue::push($_job);

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
                ['instance-id', InputArgument::REQUIRED, 'The instance to export'],
            ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                [
                    'destination',
                    'd',
                    InputOption::VALUE_OPTIONAL,
                    'The path to place the export file.',
                ],
            ]);
    }
}
