<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\ArtisanHelper;
use DreamFactory\Enterprise\Common\Traits\ArtisanOptionHelper;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models;
use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Enterprise\Services\UsageService;
use DreamFactory\Library\Utility\Json;
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
        return array_merge(parent::getOptions(),
            [
                [
                    'gather',
                    null,
                    InputOption::VALUE_NONE,
                    'When specified, all metrics are gathered and written to the database. Use when scheduling jobs.',
                ],
                [
                    'to-file',
                    'f',
                    InputOption::VALUE_REQUIRED,
                    'Write metrics to a file.',
                ],
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

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Handle the command
     *
     * @return int
     */
    public function handle()
    {
        parent::handle();

        $this->setOutputPrefix(false);

        /** @type UsageService $_service */
        $_service = \App::make(UsageServiceProvider::IOC_NAME);
        $_stats = $_service->gatherStatistics();

        if (!empty($_stats)) {
            if ($this->option('gather')) {
                Models\Metrics::where('sent_ind', 0)->update(['sent_ind' => 2]);
                Models\Metrics::create(['metrics_data_text' => $_stats,]);

                return 0;
            }

            $_output = Json::encode($_stats, JSON_UNESCAPED_SLASHES);

            if (null !== ($_file = $this->option('to-file'))) {
                file_put_contents($_file, $_output);
            }

            $this->writeln($_output);
        } else {
            $this->writeln('No metrics were gathered.');
        }

        return 0;
    }
}
