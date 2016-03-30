<?php
//******************************************************************************
//* ADS Settings
//******************************************************************************

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

return [
    'activate-by-days'         => env('DFE_ADS_ACTIVATE_BY_DAYS', ConsoleDefaults::DEFAULT_ADS_ACTIVATE_BY_DAYS),
    'activate-allowed-extends' => env('DFE_ADS_ACTIVATE_ALLOWED_EXTENDS', ConsoleDefaults::DEFAULT_ADS_ALLOWED_EXTENDS),
    'ads-allowed-idle-days'    => env('DFE_ADS_ALLOWED_INACTIVE_DAYS', ConsoleDefaults::DEFAULT_ADS_ALLOWED_IDLE_DAYS),
    /** If true, all the deactivation logic will run, but instances will *not* be deprovisioned. However, the extension count will be incremented. */
    'dry-run'                  => true,
];
