<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Console\Enums\TaskOperations;
use DreamFactory\Enterprise\Database\Models\JobResult;
use DreamFactory\Enterprise\Services\Utility\Deactivator;
use Symfony\Component\Console\Input\InputOption;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use Carbon\Carbon;

class Daily extends ConsoleCommand
{

    use Notifier;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:daily';
    /** @inheritdoc */
    protected $description = 'Performs daily maintenance tasks';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function handle()
    {
        parent::handle();

        $_results = [];
        $_tasks = config('tasks.daily', []);

        //  Grab all the task configurations and make calls based on the name
        foreach ($_tasks as $_taskName => $_taskConfig) {
            if (method_exists($this, 'do' . $_taskName . 'Tasks')) {
                $_results[$_taskName] = $this->{'do' . $_taskName . 'Tasks'}($_taskConfig);
            } else {
                \Log::warning('[dfe.daily] no method available for daily task "' . $_taskName . '"');
            }
        }

        //  Store results for posterity
        JobResult::create(['result_id_text' => 'dfe.daily.' . date('YMDHis'), 'result_text' => $_results]);
    }

    /**
     * @param string $which The task type to perform
     *
     * @return bool
     */
    protected function performTasks($which)
    {
        $_results = [];

        //  Grab all the task configurations and make calls based on the name
        if (!empty($_tasks = config('tasks.' . $which, []))) {
            foreach ($_tasks as $_taskName => $_taskConfig) {
                if (method_exists($this, 'do' . $_taskName . 'Tasks')) {
                    $_results[$_taskName] = $this->{'do' . $_taskName . 'Tasks'}($_taskConfig);
                } else {
                    \Log::notice('[dfe.daily] no method available for configured task "daily.' . $_taskName . '"');
                    $_results[$_taskName] = false;
                }
            }
        }

        //  Store results for posterity
        JobResult::create(['result_id_text' => 'dfe.daily.' . date('YMDHis'), 'result_text' => $_results]);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function doDatabaseTasks(array $config)
    {
        $_results = [];

        //  Process the configured tasks for this run
        foreach ($config as $_operation => $_taskConfig) {
            foreach ($_taskConfig as $_table => $_task) {
                $_sql = array_get($_task, 'sql');
                $_bindings = array_get($_task, 'bindings');
                $_label = array_get($_task, 'label', 'Execute "' . $_sql . '"');

                if (!empty($_sql)) {
                    try {
                        $_results[$_table] = call_user_func([\DB::class, $_operation], $_sql, $_bindings);
                        \Log::info('[dfe.daily.database.' . $_operation . '] ' . $_label);
                    } catch (\Exception $_ex) {
                        \Log::error($_results[$_table] =
                            '[dfe.daily.database.' . $_operation . '] exception: ' . $_ex->getMessage());
                    }
                }
            }
        }

        return $_results;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function doStorageTasks(array $config)
    {
        $_results = [];

        return $_results;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function doInstanceTasks(array $config)
    {
        $_results = [];

        //  Process the configured tasks for this run
        foreach ($config as $_operation => $_taskConfig) {

            switch ($_operation) {

                case TaskOperations::REMINDER:
                    if (false !== array_get($_taskConfig, 'enable', false)) {
                        $reminderInfo = Deactivator::processReminders(
                            config('ads.instance-expires-days'),
                            config('ads.reminder-days')
                        );
                    }
                    if (!empty($reminderInfo)) {
                        $this->sendReminders($reminderInfo);
                    }

                    break;

                case TaskOperations::ADS:
                    if (false !== array_get($_taskConfig, 'enable', false)) {
                        $deactivator = new Deactivator();

                        if (config('ads.deactivate-by-user') === true) {
                            $_results[$_operation] =
                                $deactivator->deprovisionInactiveUsers(
                                    config('ads.instance-expires-days'),
                                    config('ads.dry-run', true));

                        } else {
                            $_results[$_operation] =
                                $deactivator->deprovisionInactiveInstances(
                                    config('ads.instance-expires-days'),
                                    config('ads.dry-run', true));
                        }
                    }
                    break;
            }
        }

        return $_results;
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                ['dry-run', null, InputOption::VALUE_NONE, 'When specified, no instances will be deprovisioned.',],
            ]);
    }

    protected function sendReminders($reminderInfo)
    {

        foreach ($reminderInfo as $days => $data) {
            $this->notify(
                $data['email'],
                $data['display_name'],
                'DreamFactory Trial Instance Expiration',
                [
                    'instance'      => false,
                    'instanceUrl'   => 'https://' . $data['url'],
                    'instanceName'  => $data['instance_id_text'],
                    'firstName'     => $data['firstname'],
                    'contentHeader' => 'Instance Trial Expiring.',
                    'headTitle'     => 'DreamFactory Trial Expiration Notification',
                    'daysRemaining' => $data['days'],
                    'expDate'       => $data['expDate'],
                    'email-view'    => 'emails.reminder'
                ]);
        }
    }
}
