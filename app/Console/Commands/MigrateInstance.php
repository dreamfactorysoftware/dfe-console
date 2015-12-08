<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Instance\Capsule\InstanceCapsule;
use DreamFactory\Library\Utility\Disk;
use DreamFactory\Library\Utility\JsonFile;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrateInstance extends ConsoleCommand implements SelfHandling
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:migrate-instance';
    /** @inheritdoc */
    protected $description = 'Run migration for an instance.';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function fire()
    {
        parent::fire();
        $this->setOutputPrefix('[' . $this->name . ']');

        if ($this->option('all')) {
            $this->info('Migrating <comment>all</comment> instances');
            $_results = $this->migrateAllInstances();
        } elseif (null !== ($_instanceId = $this->argument('instance-id'))) {

            $this->info('Migrating instance "<comment>' . $_instanceId . '</comment>"');
            $_results = [$_instanceId => $this->migrateSingleInstance($_instanceId)];
        } else {
            $this->error('You must specify an <instance-id> or use the "--all" option.');

            return 1;
        }

        $_file = storage_path(date('YmdHis') . '-migrate-instance.json');

        if (false === JsonFile::encodeFile($_file, $_results)) {
            $this->error('Error storing results to file.');
        }

        return 0;
    }

    /**
     * Migrates all instances known, or in a cluster
     */
    protected function migrateAllInstances()
    {
        if (null !== ($_clusterId = $this->option('cluster-id'))) {
            $_instances = $this->findClusterInstances($_clusterId, ['instance_id_text']);
        } else {
            $_instances = Instance::orderBy('instance_id_text')->get(['instance_id_text']);
        }

        $_results = [];

        if (!empty($_instances)) {
            foreach ($_instances as $_instance) {
                $_id = $_instance->instance_id_text;
                try {
                    $_results[$_id] = $this->migrateSingleInstance($_id);
                    $this->info('* <comment>' . $_id . ':</comment> <info>success</info>');
                } catch (\Exception $_ex) {
                    $_results[$_id] =
                        ['success' => false, 'output' => $_ex->getMessage(), 'exit_code' => $_ex->getCode()];
                    $this->info('* <comment>' . $_id . ':</comment> <error>failure</error>');
                }
            }
        }

        return $_results;
    }

    /**
     * @param string|Instance $instanceId
     *
     * @return bool
     */
    protected function migrateSingleInstance($instanceId)
    {
        try {
            $_capsule = InstanceCapsule::make($instanceId);
        } catch (ModelNotFoundException $_ex) {
            $this->error('The instance "' . $instanceId . '" does not exist.');

            return ['success' => false, 'output' => 'Instance not found.', 'exit_code' => -1];
        }

        $_output = null;

        if (0 !== ($_result = $_capsule->call('migrate', $this->option('seed') ? ['--seed' => true] : [], $_output))) {
            \Log::error('Error result "' . $_result . '" returned', ['output' => $_output]);

            return ['success' => false, 'output' => $_output, 'exit_code' => $_result];
        }

        return ['success' => true, 'output' => $_output, 'exit_code' => $_result];
    }

    /** @inheritdoc */
    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>dfe:migrate-instance</info> command initiates a "php artisan migrate"
for an instance under management.

<info>php artisan dfe:migrate-instance [-s|--seed] [-a|--all] [-c|--cluster-id=<cluster-id>] "instance-id"</info>

EOT
        );
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['instance-id', InputArgument::OPTIONAL, 'The instance to migrate',],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                ['all', 'a', InputOption::VALUE_NONE, 'Migrate *all* cluster instances',],
                [
                    'cluster-id',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'If specified with "--all", will migrate only instances managed by "cluster-id".',
                ],
                [
                    'seed',
                    's',
                    InputOption::VALUE_NONE,
                    'If specified, "--seed" will be passed to any "migrate" commands',
                ],
            ]);
    }
}
