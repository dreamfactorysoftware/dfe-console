<?php
//******************************************************************************
//* DFE Console Task Settings
//******************************************************************************

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

return [
    //******************************************************************************
    //* Always do these tasks
    //******************************************************************************
    '*'       => [
        /** Database tasks */
        'database' => [],
        /** Storage tasks */
        'storage'  => [],
        /** Instance tasks */
        'instance' => [],
    ],
    //******************************************************************************
    //* Tasks to perform daily
    //******************************************************************************
    'daily'   => [
        /** Database tasks */
        'database' => [
            /** INSERT statements */
            'insert' => [],
            /** UPDATE statements */
            'update' => [],
            /** DELETE statements */
            'delete' => [
                'auth_reset_t'     => [
                    'label'    => 'Remove expired password resets',
                    'sql'      => 'DELETE FROM auth_reset_t WHERE DATEDIFF(CURRENT_DATE, created_at) > :keep_days',
                    'bindings' => [':keep_days' => env('DFE_RESETS_DAYS_TO_KEEP', ConsoleDefaults::DEFAULT_RESETS_DAYS_TO_KEEP)],
                ],
                'metrics_t'        => [
                    'label'    => 'Remove expired metrics bundles',
                    'sql'      => 'DELETE FROM metrics_t WHERE DATEDIFF(CURRENT_DATE, create_date) > :keep_days',
                    'bindings' => [':keep_days' => env('DFE_METRICS_DAYS_TO_KEEP', ConsoleDefaults::DEFAULT_METRICS_DAYS_TO_KEEP)],
                ],
                'metrics_detail_t' => [
                    'label'    => 'Remove expired metrics bundle details',
                    'sql'      => 'DELETE FROM metrics_detail_t WHERE DATEDIFF(CURRENT_DATE, created_at) > :keep_days',
                    'bindings' => [':keep_days' => env('DFE_METRICS_DETAIL_DAYS_TO_KEEP', ConsoleDefaults::DEFAULT_METRICS_DETAIL_DAYS_TO_KEEP)],
                ],
            ],
        ],
        /** Storage tasks */
        'storage'  => [],
        /** Instance tasks */
        'instance' => [
            /** Automated Deactivation System (ADS) */
            'ads' => [
                'enable' => true,
            ],
        ],
    ],
    //******************************************************************************
    //* Tasks to perform weekly
    //******************************************************************************
    'weekly'  => [
        /** Database tasks */
        'database' => [],
        /** Storage tasks */
        'storage'  => [],
        /** Instance tasks */
        'instance' => [],
    ],
    //******************************************************************************
    //* Tasks to perform monthly
    //******************************************************************************
    'monthly' => [
        /** Database tasks */
        'database' => [],
        /** Storage tasks */
        'storage'  => [],
        /** Instance tasks */
        'instance' => [],
    ],
    //******************************************************************************
    //* Tasks to perform yearly
    //******************************************************************************
    'yearly'  => [
        /** Database tasks */
        'database' => [],
        /** Storage tasks */
        'storage'  => [],
        /** Instance tasks */
        'instance' => [],
    ],
];
