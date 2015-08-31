<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;
use Illuminate\Contracts\Bus\SelfHandling;
use Symfony\Component\Console\Input\InputOption;

class Update extends ConsoleCommand implements SelfHandling
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:update';
    /** @inheritdoc */
    protected $description = 'Provision a new instance';

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

        $_composer = !$this->option('no-composer');

        $_instanceId = $this->argument('instance-id');

        //	Check the name here for quicker response...
        if (false === ($_instanceName = Instance::isNameAvailable($_instanceId)) || is_numeric($_instanceName[0])) {
            $this->error('The name of your instance cannot be "' .
                $_instanceId .
                '".  It is either currently in-use, or otherwise invalid.');
            exit(1);
        }

        $_ownerType = OwnerTypes::USER;
        $_ownerId = $this->argument('owner-id');
        $_guestLocation = $this->argument('guest-location');

        $_owner = $this->_locateOwner($_ownerId, $_ownerType);

        $this->writeln('Provisioning instance <comment>"' . $_instanceId . '"</comment>.');

        return \Queue::push(new ProvisionJob($_instanceId, [
            'guest-location' => $_guestLocation,
            'owner-id'       => $_owner->id,
            'owner-type'     => $_ownerType ?: OwnerTypes::USER,
            'cluster-id'     => $this->option('cluster-id'),
        ]));
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'no-composer',
                null,
                InputOption::VALUE_NONE,
                'If specified, "composer update" will not be executed.'
            ],
        ]);
    }
}
