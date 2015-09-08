<?php
//******************************************************************************
//* DFE Helper Functions
//******************************************************************************

use DreamFactory\Enterprise\Common\Providers\BaseServiceProvider;
use DreamFactory\Enterprise\Services\Auditing\Enums\AuditLevels;

if (!function_exists('ioc_name')) {
    /**
     * @param BaseServiceProvider $provider
     *
     * @return string Returns IoC name of given provider or NULL if the service does not have one
     */
    function ioc_name($provider)
    {
        return is_callable($provider) ? $provider() : null;
    }
}

if (!function_exists('audit')) {
    /**
     * @param array  $data
     * @param int    $level
     * @param string $type The type of audit entry
     *
     * @return bool
     */
    function audit($data = [], $level = AuditLevels::INFO, $type = null)
    {
        return \Audit::log($data, $level, app('request'), $type);
    }
}