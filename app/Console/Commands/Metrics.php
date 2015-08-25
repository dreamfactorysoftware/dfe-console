<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\ArtisanHelper;
use DreamFactory\Enterprise\Common\Traits\ArtisanOptionHelper;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models;
use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Enterprise\Services\UsageService;
use Symfony\Component\Console\Input\InputOption;

class Metrics extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, ArtisanOptionHelper, ArtisanHelper;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:metrics';
    /** @inheritdoc */
    protected $description = 'Gather overall system metrics';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'console-only',
                null,
                InputOption::VALUE_NONE,
                'Only gather "console" metrics',
            ],
            [
                'dashboard-only',
                null,
                InputOption::VALUE_NONE,
                'Only gather "dashboard" metrics',
            ],
            [
                'instance-only',
                null,
                InputOption::VALUE_NONE,
                'Only gather "dashboard" metrics',
            ],
        ]);
    }

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function fire()
    {
        $this->setOutputPrefix(false);

        parent::fire();

        /** @type UsageService $_service */
        $_service = \App::make(UsageServiceProvider::IOC_NAME);
        $_stats = $_service->gatherStatistics();

        if (!empty($_stats)) {
            $this->writeln(json_encode($_stats));
        }
    }
}
