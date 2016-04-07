<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Ops\Providers\OpsClientServiceProvider;
use DreamFactory\Enterprise\Console\Ops\Services\OpsClientService;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Library\Utility\Disk;
use DreamFactory\Library\Utility\Enums\GlobFlags;
use DreamFactory\Library\Utility\File;
use Log;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Provision extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

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
    public function handle()
    {
        parent::handle();

        //	Check the name here for quicker response...
        if (false === ($_instanceId = Instance::isNameAvailable($this->argument('instance-id'))) || is_numeric($_instanceId[0])) {
            Log::error('[dfe:provision] Provision failure: ' .
                ($_message = 'The instance name "' . $this->argument('instance-id') . '" is either currently in-use or otherwise invalid.'));
            $this->error($_message);

            return 1;
        }

        /** @type User $_owner */
        $_owner = $this->findOwner($this->argument('owner-id'), $this->option('owner-type'));

        $_payload = [
            'instance-id'    => $_instanceId,
            'owner-id'       => $_owner->id,
            'guest-location' => $this->argument('guest-location'),
            'owner-type'     => $_owner->owner_type_nbr,
            'packages'       => $this->getPackages(),
        ];

        $this->writeln('Provisioning instance <comment>"' . $_instanceId . '"</comment>.');

        /** @type OpsClientService $_client */
        $_client = OpsClientServiceProvider::service();

        if (false === ($_result = $_client->provision($_payload)) || !$_result->success) {
            $this->error('Error while provisioning.');

            return 1;
        }

        $this->writeln('Instance <comment>' . $_instanceId . '</comment> provisioned.');

        return 0;
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
        return array_merge(parent::getOptions(),
            [
                [
                    'owner-type',
                    't',
                    InputOption::VALUE_OPTIONAL,
                    'The "owner-id" type. Values: ' . OwnerTypes::prettyList('"', false, true),
                    OwnerTypes::USER,
                ],
                [
                    'packages',
                    'p',
                    InputOption::VALUE_OPTIONAL,
                    'A comma separated list of packages with which to provision the instance',
                    null,
                ],
                [
                    'cluster-id',
                    'c',
                    InputOption::VALUE_OPTIONAL,
                    'The cluster where this instance is to be placed.',
                    config('provisioning.default-cluster-id'),
                ],
            ]);
    }

    /**
     * Scans the input for valid packages
     *
     * @return array
     */
    protected function getPackages()
    {
        $_result = [];

        if (null === ($_packages = trim($this->option('packages')))) {
            return [];
        }

        $_packages = explode(',', $_packages);

        foreach ($_packages as $_index => $_package) {
            if (is_dir($_package = trim($_package))) {
                if (false !== ($_files = Disk::glob(scandir($_package), GlobFlags::GLOB_NODIR | GlobFlags::GLOB_NODOTS))) {
                    foreach ($_files as $_file) {
                        $_file = Disk::path([$_package, $_file]);
                        if ($this->isValidPackage($_file)) {
                            $_result[] = $_file;
                        }
                    }
                }
            }
        }

        return $_result;
    }

    /**
     * Checks to see if a package file is a valid DF package
     *
     * @param string $file The absolute path to the file to check
     *
     * @return bool
     */
    protected function isValidPackage($file)
    {
        try {
            $_zip = new \ZipArchive();

            if (true !== ($_code = $_zip->open($file))) {
                return false;
            }

            //  Make sure the package file is valid
            if (false === $_zip->getFromName('package.json')) {
                return false;
            }

            //  Close and release...
            $_zip->close();
            $_zip = null;

            return true;
        } catch (\Exception $_ex) {
            return false;
        }
    }
}
