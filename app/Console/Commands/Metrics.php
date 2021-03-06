<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\ArtisanHelper;
use DreamFactory\Enterprise\Common\Traits\ArtisanOptionHelper;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Console\Enums\ConsoleOperations;
use DreamFactory\Enterprise\Database\Models;
use DreamFactory\Enterprise\Services\Facades\License;
use DreamFactory\Enterprise\Services\Facades\Usage;
use DreamFactory\Library\Utility\Json;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Metrics extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, ArtisanOptionHelper, ArtisanHelper, Notifier;

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
            /** @var Models\Metrics $_metrics */
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

        if (!$_metrics || $this->option('force')) {
            $_stats = Usage::getMetrics([
                'send'    => !$this->option('no-usage-data'),
                'verbose' => $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE,
            ]);
        } else {
            $_stats = $_metrics->getAttribute('metrics_data_text');
            !$this->option('no-usage-data') && License::reportStatistics($_stats);
        }

        if (!empty($_stats)) {
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

                OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity() && $this->writeln($_output);
            }

            $_user = Models\ServiceUser::first();

            $this->notifyJobOwner(ConsoleOperations::METRICS,
                config('license.notification-address'),
                config('license.notification-name'),
                [
                    'firstName' => $_user->first_name_text,
                    'emailBody' => '<p>Metrics have been generated for the date ' .
                        date('Y-m-d') .
                        '.</p><p><pre>' .
                        Json::encode($_stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) .
                        '</pre></p>',
                ]);
        } else {
            $this->writeln('No metrics were gathered.');
        }

        return 0;
    }

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
}
