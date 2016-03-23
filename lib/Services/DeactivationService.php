<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use Log;

/**
 * General deactivation service
 */
class DeactivationService extends BaseService
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Deactivate instances that haven't been activated in "x" days
     *
     * @param int|null $days
     * @param int|null $extends
     *
     * @return array
     */
    public function deprovisionInactiveInstances($days = null, $extends = null)
    {
        $days = $days ?: config('dfe.activate-by-days');
        $extends = $extends ?: config('dfe.activate-allowed-extensions');

        $_sql = <<<MYSQL
SELECT 
    d.id, 
    d.instance_id,
    i.instance_id_text 
FROM 
    deactivation_t  d, 
    instance_t i 
WHERE 
    DATEDIFF(CURRENT_DATE, d.create_date) > :days AND 
    d.extend_count_nbr > :extends AND 
    d.instance_id = i.id 
ORDER BY 
    d.instance_id, d.create_date
MYSQL;

        $_rows = \DB::select($_sql, [':days' => $days, ':extends' => $extends]);

        if (empty($_rows)) {
            Log::info('[dfe.deactivation-service] no deactivations queued.');

            return [];
        }

        foreach ($_rows as $_instance) {
            $_results[$_instance->id] = \Artisan::call('dfe::deprovision', ['instance-id' => $_instance->instance_id_text]);
        }
    }
}
