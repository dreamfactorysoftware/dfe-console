<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Instance\Capsule\InstanceCapsule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Capsule extends ConsoleCommand
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
    public function handle()
    {
        parent::handle();
        $this->setOutputPrefix('[' . $this->name . ']');

        $_instanceId = $this->argument('instance-id');

        if ($this->option('destroy')) {
            InstanceCapsule::unmake($_instanceId);
            $this->info('* Instance "<comment>' . $_instanceId . '</comment>" capsule destroyed.');

            return 0;
        }

        $this->info('Encapsulating instance "<comment>' . $_instanceId . '</comment>"');

        try {
            $_capsule = InstanceCapsule::make($_instanceId, false);
            $this->info('* Instance "<comment>' . $_instanceId . '</comment>" encapsulated in <comment>' . $_capsule->getCapsulePath() . '</comment>.');

            return 0;
        } catch (ModelNotFoundException $_ex) {
            $this->error('The instance "' . $_instanceId . '" does not exist.');

            return 1;
        }
    }

    /** @inheritdoc */
    protected function configure()
    {
        $this->setHelp(<<<EOT
            The <info>dfe:capsule</info> command encapsulates a managed instance for direct access.

<info>php artisan dfe:capsule <comment><instance-id></comment> [-d|--destroy]</info>

EOT
        );
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['instance-id', InputArgument::REQUIRED, 'The instance to encapsulate',],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                ['destroy', 'd', InputOption::VALUE_NONE, 'Destroys a previously created capsule.',],
            ]);
    }
}
