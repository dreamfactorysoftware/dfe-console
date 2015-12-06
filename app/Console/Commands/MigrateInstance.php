<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Instance\Capsule\InstanceCapsule;
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

        $_instanceId = $this->argument('instance-id');

        try {
            $_capsule = InstanceCapsule::make($_instanceId);
        } catch (ModelNotFoundException $_ex) {
            $this->error('The instance "' . $_instanceId . '" does not exist.');

            return 1;
        }

        $_output = null;

        if (0 !== ($_result = $_capsule->call('migrate', $this->option('seed') ? ['--seed'] : [], $_output))) {
            \Log::error('Error result "' . $_result . '" returned', ['output' => $_output]);
        } else {
            logger('[dfe.migrate-instance] "' . $_instanceId . '"', ['output' => $_output]);
        }

        $_capsule->down();

        return 0;
    }

    /** @inheritdoc */
    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>dfe:migrate-instance</info> command initiates a "php artisan migrate"
for an instance under management.

<info>php artisan dfe:migrate-instance [--seed] "instance-id"</info>

EOT
        );
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['instance-id', InputArgument::REQUIRED, 'The instance to migrate',],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['seed', null, InputOption::VALUE_NONE, 'If specified, "--seed" will be passed to any "migrate" commands',],
        ]);
    }
}
