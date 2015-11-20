<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Services\GitService;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
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

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /** @inheritdoc */
    public function boot()
    {
        parent::boot();

        //  Make sure our repo path exists
        $this->repoBase =
            Disk::path([config('dfe.blueprints.path', ConsoleDefaults::DEFAULT_BLUEPRINT_REPO_PATH)], true);

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
     */
    public function make($instanceId, $options = [])
    {
        $_instance = $this->findInstance($instanceId);
        $_client = InstanceApiClient::connect($_instance);

//        $_payload = [
//            'email'       => array_get($options, 'email'),
//            'password'    => array_get($options, 'password'),
//            'remember_me' => array_get($options, 'remember_me', false),
//        ];

        $_blueprint = ['instance' => $_instance->toArray()];
        $_resources = [];

        //  Get services
        $_result = $_client->resources();

        foreach ($_result as $_resource) {
            $_resources[$_resource->name] = [];

            try {
                $_response = $_client->get($_resource->name);
                $_resources[$_resource->name] =
                    isset($_response->resource) ? $_response->resource : $_response;
            } catch (\Exception $_ex) {
            }
        }

        $_blueprint['resources'] = $_resources;

        //  Do not commit this blueprint
        if (array_get($options, 'commit', true)) {
            return $this->commitBlueprint($instanceId, $_blueprint);
        }

        return $_blueprint;
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
        JsonFile::encodeFile($_file, $blueprint);

        //  Commit it
        $_commitMessage = 'Blueprint created on ' . date('Y-m-d H:i:s');

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
        } else {
            \Event::fire('dfe.blueprint.post-commit',
                ['instance-id' => $instanceId, 'blueprint' => $blueprint, 'repository-path' => $this->repoPath,]);
        }

        return $blueprint;
    }
}
