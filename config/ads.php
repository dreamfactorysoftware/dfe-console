<?php
//******************************************************************************
//* ADS Settings
//******************************************************************************

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

return [
    /** days until an automatic delete of the instance is processed */
    'instance-expires-days'    => env('DFE_ADS_INSTANCE_EXPIRES_DAYS', 30),
    /** Days to send reminders before Expires days (expires-days - reminder-days) Takes an array. */
    'reminder-days'            => env('DFE_ADS_REMINDER_DAYS', [10, 3]),
    /** If true, all the deactivation logic will run, but instances will *not* be deprovisioned. However, the extension count will be incremented. */
    'dry-run'                  => true,
];
