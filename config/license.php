<?php
//******************************************************************************
//* Licensing
//******************************************************************************
return [
    /** If true, each instance's resource counts are sent with anonymous stats */
    'send-instance-details' => false,
    'bcc-notifications'     => false,
    'notification-address'  => 'ops@dreamfactory.com',
    'notification-name'     => 'DFE Operations',
    /** The endpoints for license server services */
    'endpoints'             => [
        'install'  => 'http://license.dreamfactory.com/register/install',
        'admin'    => 'http://license.dreamfactory.com/register/admin',
        'instance' => 'http://license.dreamfactory.com/register/instance',
        'usage'    => 'http://license.dreamfactory.com/register/usage',
    ],
];
