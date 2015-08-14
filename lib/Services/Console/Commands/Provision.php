<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Provision extends ConsoleCommand
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Command name
     */
    protected $name = 'dfe:provision';
    /**
     * @type string Command description
     */
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

        $_instanceId = $this->argument('instance-id');

        //	Check the name here for quicker response...
        if (false === ($_instanceName = Instance::isNameAvailable($_instanceId)) || is_numeric($_instanceName[0])) {
            $this->error('The name of your instance cannot be "' . $_instanceId . '".  It is either currently in-use, or otherwise invalid.');
            exit(1);
        }

        $_ownerType = OwnerTypes::USER;
        $_ownerId = $this->argument('owner-id');
        $_guestLocation = $this->argument('guest-location');

        try {
            $_owner = OwnerTypes::getOwner($_ownerId, $_ownerType);
        } catch (\Exception $_ex) {
            try {
                $_owner = User::byEmail($_ownerId);
            } catch (\Exception $_ex) {
                throw new \InvalidArgumentException('The owner-id "' . $_ownerId . '" could not be found.');
            }
        }

        if (empty($_owner)) {
            throw new \InvalidArgumentException('The owner-id "' . $_ownerId . '" is not valid.');
        }

        $this->writeln('Provisioning instance <comment>"' . $_instanceId . '"</comment>.');

        return \Queue::push(new ProvisionJob($_instanceId, [
            'guest-location' => $_guestLocation,
            'owner-id'       => $_owner->id,
            'owner-type'     => $_ownerType,
            'cluster-id'     => $this->option('cluster-id'),
        ]));
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
                ['owner-id', InputArgument::REQUIRED, 'The id of the owner of the new instance'],
                ['instance-id', InputArgument::REQUIRED, 'The name of the new instance'],
                [
                    'guest-location',
                    InputArgument::OPTIONAL,
                    'The location of the new instance. Values: ' . GuestLocations::prettyList('"', true),
                    config('provisioning.default-guest-location'),
                ],
            ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getArguments(),
            [
                [
                    'cluster-id',
                    'c',
                    InputOption::VALUE_OPTIONAL,
                    'The cluster where this instance is to be placed.',
                    config('provisioning.default-cluster-id'),
                ],
            ]);
    }
}
