<?php namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Console\Ops\Facades\OpsClient;

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
     * @param int|null $days
     * @param int|null $extends
     * @param bool     $dryRun If true, go through the motions but do not deprovision instances.
     *
     * @return array
     */
    public static function deprovisionInactiveInstances($days = null, $extends = null, $dryRun = false)
    {
        $days = $days ?: config('dfe.activate-by-days');
        $extends = $extends ?: config('dfe.activate-allowed-extensions');

        $_sql = <<<MYSQL
SELECT 
    d.id, 
    d.instance_id,
    d.extend_count_nbr,
    i.instance_id_text 
FROM 
    deactivation_t  d, 
    instance_t i 
WHERE 
    DATEDIFF(CURRENT_DATE, d.create_date) > :days AND 
    d.instance_id = i.id 
ORDER BY 
    d.instance_id, d.create_date
MYSQL;

        $_results = [];
        $_count = $_errors = 0;
        $_rows = \DB::select($_sql, [':days' => $days]);

        if (!empty($_rows)) {
            foreach ($_rows as $_instance) {
                if (!empty($extends) && $_instance->extend_count_nbr <= $extends) {
                    continue;
                }

                logger('[dfe.deactivation-service] auto-deprovisioning instance "' . $_instance->instance_id_text . '"');

                $_result = null;

                try {
                    if (false === $dryRun) {
                        $_result = OpsClient::deprovision(['instance-id' => $_instance->instance_id_text,]);
                    } else {
                        $_result = new \stdClass();
                        $_result->success = true;
                    }

                    if (false === $_result || !is_object($_result) || !$_result->success) {
                        $_errors++;
                        \Log::error('[dfe.deactivation-service] * error deprovisioning "' . $_instance->instance_id_text . '"');
                    }
                } catch (\Exception $_ex) {
                    $_errors++;
                    \Log::error('[dfe.deactivation-service] * exception deprovisioning "' . $_instance->instance_id_text . '": ' . $_ex->getMessage());
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
}
