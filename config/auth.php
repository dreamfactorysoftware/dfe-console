<?php
//******************************************************************************
//* Authentication Configuration
//******************************************************************************

return [
    'driver'            => 'console',
    'model'             => 'DreamFactory\\Enterprise\\Database\\Models\\ServiceUser',
    'table'             => 'service_user_t',
    'password'          => [
        'email'  => 'dfe-common::emails.password',
        'table'  => 'auth_reset_t',
        'expire' => 60,
    ],
    //  If true, users may self-register. Otherwise, admins must create users */
    'open-registration' => env('DFE_OPEN_REGISTRATION', false),
];
