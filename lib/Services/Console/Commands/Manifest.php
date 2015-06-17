<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\ServerTypes;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Commands\ManifestJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Manifest extends Command
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
    protected $_job = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function fire()
    {
        if ($this->option('create') && $this->option('show')) {
            throw new \InvalidArgumentException(
                'The --create and --show commands are mutually exclusive. You may choose one or the other, but not both.'
            );
        }

        $this->_job = new ManifestJob(
            $this->argument('cluster-id'),
            $this->argument('web-server-id'),
            ServerTypes::WEB
        );

        $this->_job
            ->setOutput($this->output)
            ->setInput($this->input)
            ->setOwnerId($this->option('owner-id'))
            ->setOwnerType($this->option('owner-type'))
            ->setShowManifest($this->option('show'))
            ->setCreateManifest($this->option('create'))
            ->setNoKeys($this->option('no-keys'));

        $_output = $this->argument('output-file');
        $this->_job->setOutputFile(!empty($_output) ? $_output : null);

        \Queue::push($this->_job);

        return $this->_job->getResult();
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(
            parent::getArguments(),
            [
                ['cluster-id', InputArgument::REQUIRED, 'The id/name of the cluster',],
                ['web-server-id', InputArgument::REQUIRED, 'The id/name of the web server from "cluster-id"',],
                [
                    'output-file',
                    InputArgument::OPTIONAL,
                    'The /path/to/manifest/file to write. Otherwise it is written to the current working directory.',
                ],
            ]
        );
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['create', 'c', InputOption::VALUE_NONE, 'Create a new manifest file. This is the default.'],
                ['no-keys', 'k', InputOption::VALUE_NONE, 'If specified, no application keys will be generated.'],
                [
                    'show',
                    's',
                    InputOption::VALUE_NONE,
                    'If specified, show the contents of an installation\'s manifest.'
                ],
                ['owner-id', null, InputOption::VALUE_REQUIRED, 'The owner id for the manifest key if not 0', 0],
                [
                    'owner-type',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'The owner type for the manifest key if not "dashboard"',
                    'dashboard'
                ],
            ]
        );
    }

    /**
     * @param int|string $clusterId
     *
     * @return string Return the id/name of the cluster involved in this job
     */
    public function getCluster($clusterId = null)
    {
        return $this->_findCluster($clusterId ?: $this->_job->getClusterId());
    }

    /**
     * @param int|string $serverId
     *
     * @return string Return the id/name of the server involved in this job
     */
    public function getServer($serverId = null)
    {
        return $this->_findCluster($serverId ?: $this->_job->getServerId());
    }

    /**
     * @return string Return the absolute path of the output file
     */
    public function getOutputFile()
    {
        return $this->_job->getOutputFile();
    }

    /**
     * @return Cluster|Instance|Server|User|ServiceUser|\stdClass the owner of the manifest key
     */
    public function getOwner()
    {
        return $this->_locateOwner($this->_job->getOwnerId(), $this->_job->getOwnerType());
    }

}
