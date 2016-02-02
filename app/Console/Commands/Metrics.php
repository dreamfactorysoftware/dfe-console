<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\ArtisanHelper;
use DreamFactory\Enterprise\Common\Traits\ArtisanOptionHelper;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models;
use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Enterprise\Services\UsageService;
use DreamFactory\Library\Utility\Curl;
use DreamFactory\Library\Utility\Json;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
                [
                    'no-usage-data',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Do not send usage data if true',
                    false,
                ],
                [
                    'force',
                    null,
                    InputOption::VALUE_NONE,
                    'Force overwrite of daily gather',
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

        $_sent = false;

        try {
            if (null !== ($_metrics = Models\Metrics::whereRaw('DATE(create_date) = :create_date', [':create_date' => date('Y-m-d'),])->firstOrFail())) {
                if (!$this->option('force') && $this->option('gather')) {
                    $this->output->error('Gather request would overwrite existing metrics for ' . date('Y-m-d') . '. Use --force to overwrite.');

                    return 0;
                }

                if (!$this->option('force')) {
                    $this->writeln('Existing metrics loaded.');
                }
            }
        } catch (ModelNotFoundException $_ex) {
            //  No data, we cool
            $_metrics = null;
        }

        /** @type UsageService $_service */
        if (!$_metrics || $this->option('force')) {
            $_service = UsageServiceProvider::service();
            $_stats = $_service->gatherStatistics();
        } else {
            $_stats = $_metrics->getAttribute('metrics_data_text');
        }

        if (!empty($_stats)) {
            if (!$this->input->getOption('no-usage-data')) {
                $_sent = $this->sendUsageData($_stats);
            }

            if ($this->option('gather')) {
                Models\Metrics::where('sent_ind', 0)->update(['sent_ind' => 2]);

                if (null !== $_metrics) {
                    $_metrics->update(['metrics_data_text' => $_stats, 'sent_ind' => $_sent,]);
                } else {
                    Models\Metrics::create(['metrics_data_text' => $_stats, 'sent_ind' => $_sent,]);
                }
            } else {
                $_output = Json::encode($_stats, JSON_UNESCAPED_SLASHES);

                if (null !== ($_file = $this->option('to-file'))) {
                    file_put_contents($_file, $_output);
                }

                $this->writeln($_output);
            }
        } else {
            $this->writeln('No metrics were gathered.');
        }

        return 0;
    }

    /**
     * @param array $stats
     *
     * @return bool
     */
    protected function sendUsageData(array $stats)
    {
        if (null !== ($_endpoint = config('license.endpoints.usage'))) {
            try {
                if (false === ($_result = Curl::post($_endpoint, json_encode($stats), [CURLOPT_HTTPHEADER => ['Content-Type: application/json']]))) {
                    throw new \RuntimeException('Network error during post.');
                }

                \Log::info('[dfe:metrics] usage data sent to ' . $_endpoint, $stats);

                return true;
            } catch (\Exception $_ex) {
                \Log::error('[dfe:metrics] exception reporting usage data: ' . $_ex->getMessage());
            }
        }

        return false;
    }
}
