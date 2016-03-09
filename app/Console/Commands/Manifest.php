<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Enums\ServerTypes;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Jobs\ManifestJob;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Manifest extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @var string The console command name */
    protected $name = 'dfe:manifest';
    /**  @var string The console command description */
    protected $description = 'Generates a cluster manifest file (.dfe.cluster.json) for DFE installations.';
    /** @type ManifestJob */
    protected $job;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function handle()
    {
        parent::handle();

        if ($this->option('create') && $this->option('show')) {
            throw new InvalidArgumentException('The --create and --show commands are mutually exclusive. You may choose one or the other, but not both.');
        }

        $this->job = new ManifestJob($this->argument('cluster-id'), $this->argument('web-server-id'), ServerTypes::WEB);

        $this->job->setInput($this->input)
            ->setOutput($this->output)
            ->setOwner($this->option('owner-id'), $this->option('owner-type'))
            ->setShowManifest($this->option('show'))
            ->setCreateManifest($this->option('create'))
            ->setNoKeys($this->option('no-keys'));

        $_output = $this->argument('output-file');
        $this->job->setOutputFile(!empty($_output) ? $_output : null);

        $this->dispatch($this->job);

        return $this->job->getResult();
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['cluster-id', InputArgument::REQUIRED, 'The id/name of the cluster',],
                ['web-server-id', InputArgument::REQUIRED, 'The id/name of the web server from "cluster-id"',],
                [
                    'output-file',
                    InputArgument::OPTIONAL,
                    'The /path/to/manifest/file to write. Otherwise it is written to the current working directory.',
                ],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                ['create', 'c', InputOption::VALUE_NONE, 'Create a new manifest file. This is the default.'],
                ['no-keys', 'k', InputOption::VALUE_NONE, 'If specified, no application keys will be generated.'],
                [
                    'show',
                    's',
                    InputOption::VALUE_NONE,
                    'If specified, show the contents of an installation\'s manifest.',
                ],
                ['owner-id', null, InputOption::VALUE_REQUIRED, 'The owner id for the manifest key if not 0', 0],
                [
                    'owner-type',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'The owner type for the manifest key if not "dashboard"',
                    'dashboard',
                ],
            ]);
    }

    /**
     * @param int|string $clusterId
     *
     * @return \DreamFactory\Enterprise\Database\Models\Cluster
     */
    public function getCluster($clusterId = null)
    {
        return $this->_findCluster($clusterId ?: $this->job->getClusterId());
    }

    /**
     * @param int|string $serverId
     *
     * @return \DreamFactory\Enterprise\Database\Models\Server
     */
    public function getServer($serverId = null)
    {
        return $this->_findCluster($serverId ?: $this->job->getServerId());
    }

    /**
     * @return string Return the absolute path of the output file
     */
    public function getOutputFile()
    {
        return $this->job->getOutputFile();
    }

    /**
     * @return \DreamFactory\Enterprise\Database\Models\Cluster|\DreamFactory\Enterprise\Database\Models\Instance|\DreamFactory\Enterprise\Database\Models\Server|\DreamFactory\Enterprise\Database\Models\User|\DreamFactory\Enterprise\Database\Models\ServiceUser|\stdClass the owner of the manifest key
     */
    public function getOwner()
    {
        $_owner = $this->job->getOwner();

        return $this->_locateOwner($_owner->id, $_owner->owner_type_nbr);
    }
}
