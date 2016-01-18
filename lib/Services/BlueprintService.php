<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Services\GitService;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Instance\Ops\Facades\InstanceApiClient;
use DreamFactory\Library\Utility\Disk;
use DreamFactory\Library\Utility\JsonFile;

/**
 * General blueprint services
 */
class BlueprintService extends BaseService
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type GitService
     */
    protected $git;
    /**
     * @type string The repository base (absolute)
     */
    protected $repoBase;
    /**
     * @type string The repository path (relative to $repoBase)
     */
    protected $repoPath;
    /**
     * @type array Default resources to be excluded from blueprint
     */
    protected $filters = [
        //  Skip pre-installed apps
        'app'            => [
            'key'     => 'name',
            'exclude' => ['admin', 'swagger', 'filemanager',],
        ],
        //  Skip first admin user
        'admin'          => [
            'key'     => 'id',
            'exclude' => [1,],
        ],
        'email_template' => [
            'key'     => 'id',
            'exclude' => [1, 2, 3,],
        ],
        'script_type'    => [
            'key'     => 'name',
            'exclude' => ['nodejs', 'php', 'v8js',],
        ],
        'service'        => [
            'key'     => 'name',
            'exclude' => ['system', 'api_docs', 'files', 'db', 'email', 'user',],
        ],
    ];

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /** @inheritdoc */
    public function boot()
    {
        parent::boot();

        //  Make sure our repo path exists
        $this->repoBase = Disk::path([config('dfe.blueprints.path', ConsoleDefaults::DEFAULT_BLUEPRINT_REPO_PATH)], true);

        //  Tack on the cluster for the organization
        $this->repoPath = Disk::path([$this->repoBase, $_repoName = config('dfe.cluster-id', gethostname()),], true);

        //  Get an instance of the git service if we don't have one yet
        !$this->git && $this->git = new GitService($this->app, $this->repoBase, $_repoName);

        //  Initialize it as a git repo if it isn't already...
        $this->git->init();
    }

    /**
     * Returns a blueprint for an instance
     *
     * @param string $instanceId The instance to map
     * @param array  $options    Options ['user'=['email','password','remember_me'], 'commit' =>true|false,]
     *
     * @return array
     * @throws
     */
    public function make($instanceId, $options = [])
    {
        $_header = null;
        $_instance = $this->findInstance($instanceId);

        if (null !== ($_key = array_get($options, 'api-key'))) {
            $_header = EnterpriseDefaults::INSTANCE_API_HEADER;
        }

        if (config('dfe.blueprints.login-required')) {
            throw new \RuntimeException('This feature is not implemented');
        }

        $_client = InstanceApiClient::connect($_instance, $_key, $_header);

        //  Get services
        if (false !== ($_result = $_client->resources())) {
            $_blueprint = ['instance' => $_instance->toArray(), 'resources' => [], 'database' => [],];

            foreach ($_result as $_resource) {
                $_name = is_object($_resource) ? $_resource->name : $_resource;

                try {
                    $_response = $_client->resource($_name);
                    $_blueprint['resources'][$_name] = $this->filterResources($_name, $_response);
                } catch (\Exception $_ex) {
                }
            }

            //  Get database
            $_blueprint['database'] = $this->getStoredData($_instance);

            //  Optionally commit and return...
            array_get($options, 'commit', true) && $this->commitBlueprint($instanceId, $_blueprint);

            return $_blueprint;
        }

        return false;
    }

    /**
     * @param string $resource
     * @param array  $data
     *
     * @return array|bool
     */
    protected function filterResources($resource, $data)
    {
        if (null === ($_excluded = array_get($this->filters, $resource))) {
            return $data;
        }

        if (null !== ($_excludeKey = data_get($_excluded, 'key'))) {
            $_excluded = data_get($_excluded, 'exclude');
        }

        foreach ($data as $_id => $_values) {
            //  Check for exclusions (default installed stuff)
            if ($_excluded && in_array(data_get($_values, $_excludeKey), $_excluded)) {
                array_forget($data, $_id);
            }
        }

        return $data;
    }

    /**
     * @param Instance $instance
     *
     * @return array
     */
    protected function getStoredData(Instance $instance)
    {
        //  Get our database connection...
        $_db = $instance->instanceConnection($instance);

        //  Get a table list...
        $_tables = $_db->getDoctrineConnection()->getSchemaManager()->listTables();

        $_data = [];

        foreach ($_tables as $_table) {
            $_tableName = $_table->getName();

            //  Get the columns
            $_columns = [];

            foreach ($_table->getColumns() as $_column) {
                $_array = $_column->toArray();
                $_array['type'] = (string)$_array['type'];
                $_columns[] = $_array;
            }

            $_data[$_tableName] = [
                'schema' => $_columns,
                'data'   => $_db->select('SELECT * FROM ' . $_tableName),
            ];
        }

        return $_data;
    }

    /**
     * Commits a blueprint to the repo
     *
     * @param string $instanceId
     * @param array  $blueprint
     *
     * @return bool
     */
    protected function commitBlueprint($instanceId, $blueprint)
    {
        //  Create/update the file
        $_file = Disk::path([$this->repoPath, $instanceId . '.json',]);
        JsonFile::encodeFile($_file, $blueprint, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        //  Commit it
        $_commitMessage = 'Blueprint for instance "' . $instanceId . '" created on ' . date('Y-m-d H:i:s');

        $_response = \Event::fire('dfe.blueprint.pre-commit',
            [
                'instance-id'     => $instanceId,
                'blueprint'       => $blueprint,
                'repository-path' => $this->repoPath,
                'commit-message'  => $_commitMessage,
            ]);

        //  See if the commit message has changed...
        if (!empty($_response) && null !== ($_message = array_get($_response, 'commit-message'))) {
            $_commitMessage = $_message;
        }

        if (0 !== $this->git->commitChange($_file, $_commitMessage)) {
            \Log::error('Error committing blueprint file "' . $_file . '".');

            return false;
        }

        \Event::fire('dfe.blueprint.post-commit',
            ['instance-id' => $instanceId, 'blueprint' => $blueprint, 'repository-path' => $this->repoPath,]);

        return true;
    }
}
