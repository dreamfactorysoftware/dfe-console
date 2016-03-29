<?php namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Common\Enums\OperationalStates;
use DreamFactory\Enterprise\Console\Ops\Facades\OpsClient;
use DreamFactory\Enterprise\Database\Enums\DeactivationReasons;
use DreamFactory\Enterprise\Database\Models\EnterpriseModel;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;

/**
 * General deprovisioner
 */
class Deactivator
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Deactivate instances that haven't been activated in "x" days
     *
     * @param int|null   $days
     * @param int|null   $extends
     * @param bool       $dryRun If true, go through the motions but do not deprovision instances.
     * @param array|null $ids    An array of one or more instance-id's that should only be considered
     *
     * @return array
     */
    public static function deprovisionInactiveInstances($days = null, $extends = null, $dryRun = false, $ids = null)
    {
        $_results = [];
        $_count = $_errors = 0;

        $days = $days ?: config('dfe.activate-by-days');
        $extends = $extends ?: config('dfe.activate-allowed-extensions');

        $_rows = Instance::eligibleDeactivations($days, $ids);

        if (!empty($_rows)) {
            foreach ($_rows as $_instance) {
                if (!empty($extends) && $_instance->extend_count_nbr <= $extends) {
                    continue;
                }

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

        \Log::notice('[dfe.deactivation-service] processed ' . number_format($_count, 0) . ' deactivations with ' . number_format($_errors, 0) . ' error(s)');

        return $_results;
    }

    /**
     * Instructs an instance to deprovision itself
     *
     * @param \DreamFactory\Enterprise\Database\Models\Instance|EnterpriseModel $instance The instance
     * @param bool                                                              $dryRun   If true, don't actually deprovision but put into DEACTIVATED state.
     * @param int                                                               $state    The platform state to set when deactivation on a dry run
     *
     * @return array
     */
    public static function selfDestruct($instance, $dryRun = false, $state = OperationalStates::DEACTIVATED)
    {
        logger('[dfe.deactivator.self-destruct] deprovisioning instance "' .
            $instance->instance_id_text .
            '" created on ' .
            $instance->create_date .
            ' with ' .
            $instance->extend_count_nbr .
            ' extension(s)');

        try {
            if (false === $dryRun) {
                $_result = OpsClient::deprovision(['instance-id' => $instance->instance_id_text,]);
            } else {
                //  Deactivate if activated...
                if ($instance->activate_ind && !Instance::find($instance->instance_id)->update(['activate_ind' => false, 'platform_state_nbr' => $state])) {
                    throw new ProvisioningException('[dfe.deactivator.self-destruct] * error updating state of "' . $instance->instance_id_text);
                }

                $_result = new \stdClass();
                $_result->success = true;
            }

            if (false === $_result || !is_object($_result) || !$_result->success) {
                throw new ProvisioningException('error deprovisioning instance-id "' . $instance->instance_id_text . '", check log for details');
            }
        } catch (\Exception $_ex) {
            $_result = false;
            \Log::error('[dfe.deactivator.self-destruct] * exception deprovisioning "' . $instance->instance_id_text . '": ' . $_ex->getMessage());
        }

        return $_result;
    }
}
