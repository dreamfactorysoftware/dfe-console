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

class Capsule extends ConsoleCommand implements SelfHandling
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:capsule';
    /** @inheritdoc */
    protected $description = 'Encapsulate a managed instance for direct access.';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function fire()
    {
        parent::fire();
        $this->setOutputPrefix('[' . $this->name . ']');

        if (null !== ($_instanceId = $this->argument('instance-id'))) {
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
     * @param string|Instance $instanceId
     *
     * @return bool
     */
    protected function encapsulateInstance($instanceId)
    {
        try {
            $_capsule = InstanceCapsule::make($instanceId);
        } catch (ModelNotFoundException $_ex) {
            $this->error('The instance "' . $instanceId . '" does not exist.');

            return false;
        }

        $this->debug('* Instance "'.$instanceId.'" encapsulated.');

        return true;
    }

    /** @inheritdoc */
    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>dfe:capsule</info> command encapsulates a managed instance for direct access.

<info>php artisan dfe:capsule <comment><instance-id></comment></info>

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
        return array_merge(parent::getOptions(), [
            ['all', 'a', InputOption::VALUE_NONE, 'Migrate *all* cluster instances',],
            ['cluster-id', 'c', InputOption::VALUE_REQUIRED, 'If specified with "--all", will migrate only instances managed by "cluster-id".',],
            ['seed', 's', InputOption::VALUE_NONE, 'If specified, "--seed" will be passed to any "migrate" commands',],
        ]);
    }
}
