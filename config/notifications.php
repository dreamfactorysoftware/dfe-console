<?php
//******************************************************************************
//* DFE System Notification Options
//******************************************************************************
use DreamFactory\Enterprise\Console\Enums\ConsoleOperations;

return [
    'templates' => [
        ConsoleOperations::METRICS     => [
            'view'    => 'emails.generic',
            'subject' => 'Daily Metrics',
            'title'   => 'Metrics have been generated successfully',
        ],
        ConsoleOperations::PROVISION   => [
            'view'    => 'emails.provision',
            'subject' => 'DreamFactory™ Instance Created',
            'title'   => 'Your new instance is ready',
        ],
        ConsoleOperations::DEPROVISION => [
            'view'    => 'emails.deprovision',
            'subject' => 'DreamFactory™ Instance Deleted',
            'title'   => 'Your instance has been retired',
        ],
        ConsoleOperations::EXPORT      => [
            'view'    => 'emails.export',
            'subject' => 'DreamFactory™ Instance Exported',
            'title'   => 'Your export is complete',
        ],
        ConsoleOperations::UPLOAD      => [
            'view'    => 'emails.import',
            'subject' => 'DreamFactory™ Instance Created',
            'title'   => 'Your imported instance is ready',
        ],
        ConsoleOperations::IMPORT      => [
            'view'    => 'emails.import',
            'subject' => 'DreamFactory™ Instance Created',
            'title'   => 'Your imported instance is ready',
        ],
    ],
];
