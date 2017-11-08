<?php namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Common\Enums\OperationalStates;
use DreamFactory\Enterprise\Console\Ops\Facades\OpsClient;
use DreamFactory\Enterprise\Database\Enums\DeactivationReasons;
use DreamFactory\Enterprise\Database\Models\EnterpriseModel;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Carbon\Carbon;
use League\Flysystem\Exception;

/**
 * General deprovisioner
 */
class Deactivator
{

    use DispatchesJobs;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Deactivate instances that have past the expiration for trial
     *
     * @param int|null   $expiresDays Days past creation date where instances become eligible for removal.
     * @param bool       $dryRun      If true, go through the motions but do not deprovision instances.
     * @param array|null $ids         An array of one or more instance-id's that should only be considered
     *
     * @return array
     */
    public function deprovisionInactiveInstances($expiresDays = null, $dryRun = false, $ids = null)
    {
        $_results = [];
        $_count = $_errors = 0;

        $expiresDays = $expiresDays ?: config('ads.instance-expires-days');

        $_rows = Instance::eligibleDeactivations($expiresDays, $ids);

        if (!empty($_rows)) {
            foreach ($_rows as $_instance) {

                if (false === ($_result = static::selfDestruct($_instance, $dryRun, OperationalStates::DEACTIVATED))) {
                    $_errors++;
                }

                $_results[$_instance->instance_id_text] = [
                    'job-data' => $_instance,
                    'result'   => $_result,
                    'id'       => $_count++,
                ];

                unset($_result, $_instance);
            }
        }

        \Log::notice('[dfe.deactivation-service] processed ' .
            number_format($_count, 0) .
            ' deactivations with ' .
            number_format($_errors, 0) .
            ' error(s)');

        return $_results;
    }

    public function deprovisionInactiveUsers($expiresDays = null, $dryRun = false, $ids = null)
    {

        $_count = $_errors = 0;
        $_results = [];

        $users = User::eligibleDeactivations($expiresDays);

        /** Now see if those users have any instances we need to clean up first. */
        foreach ($users as $user) {
            $instances = $user->instances;
            foreach ($instances as $instance) {
                \Log::notice('[dfe.deactivation-users]' .
                    sprintf(' User instance %s is being removed for user: %s', $instance->instance_name_text,
                        $user->email_addr_text));

                $this->dispatch(new DeprovisionJob($instance->instance_name_text));
            }
            \Log::notice('[dfe.deactivation-users]' .
                sprintf(' User %s is being removed - email: %s', $user->id, $user->email_addr_text));
        }

        $userIds = $users->pluck('id')->toArray();

        User::destroy($userIds);

        return $_results;
    }

    public static function processReminders($expiresDays = null, $reminderDays = [])
    {

        $expiresDays = $expiresDays ?: config('ads.instance-expires-days');
        $reminderDays = $reminderDays ?: config('ads.instance-reminder-days');
        $reminderInfo = [];

        /** this will iterate through the number of days prior to expiration and gather qualifying instances $days */
        foreach ($reminderDays as $days) {
            if (!is_int($days)) {
                throw new \Exception('Config ads.instance-reminder-days must be an integer!');
            }
            $_rows = Instance::getEligibleReminders(($expiresDays - $days));
            if ($_rows) {
                $instanceDataText = json_decode($_rows->instance_data_text, true);
                $dt = Carbon::parse($_rows->create_date);
                $exp = $dt->addDays($expiresDays);
                $reminderInfo[$days] = [
                    'days'               => $days,
                    'expDate'            => $exp->format('M j, Y'),
                    'instance_id_text'   => $_rows->instance_id_text,
                    'instance_data_text' => $instanceDataText,
                    'firstname'          => $_rows->firstname,
                    'display_name'       => $_rows->firstname,
                    'email'              => $_rows->email,
                    'url'                => $instanceDataText['env']['instance-id'] .
                        '.' .
                        $instanceDataText['env']['default-domain'],

                ];
                logger('[dfe.deactivator.processReminders] Deactivation ' .
                    $days .
                    ' day reminder sent for instance "' .
                    $_rows->instance_id_text .
                    '" created on ' .
                    $_rows->create_date);
            }
        }

        return $reminderInfo;
    }

    /**
     * Instructs an instance to deprovision itself
     *
     * @param \DreamFactory\Enterprise\Database\Models\Instance|EnterpriseModel $instance The instance
     * @param bool                                                              $dryRun   If true, don't actually
     *                                                                                    deprovision but put into
     *                                                                                    DEACTIVATED state.
     * @param int                                                               $state    The platform state to set
     *                                                                                    when deactivation on a dry
     *                                                                                    run
     *
     * @return array
     */
    public static function selfDestruct($instance, $dryRun = false, $state = OperationalStates::DEACTIVATED)
    {
        logger('[dfe.deactivator.self-destruct] deprovisioning instance "' .
            $instance->instance_id_text .
            '" created on ' .
            $instance->create_date);

        try {
            if (false === $dryRun) {
                $_result = \Artisan::call('dfe:deprovision', ['instance-id' => $instance->instance_id_text]);
            } else {
                $_result = new \stdClass();
                $_result->success = true;
            }

            if (false === $_result || !is_object($_result) || !$_result->success) {
                throw new ProvisioningException('error deprovisioning instance-id "' .
                    $instance->instance_id_text .
                    '", check log for details');
            }
        } catch (\Exception $_ex) {
            $_result = false;
            \Log::error('[dfe.deactivator.self-destruct] * exception deprovisioning "' .
                $instance->instance_id_text .
                '": ' .
                $_ex->getMessage());
        }

        return $_result;
    }
}
