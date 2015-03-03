<?php
//******************************************************************************
//* Authentication Configuration
//******************************************************************************

return [
    'driver'   => 'eloquent',
    'model'    => 'DreamFactory\Enterprise\Console\User',
    'table'    => 'service_user_t',
    'password' => [
        'email'  => 'emails.password',
        'table'  => 'auth_reset_t',
        'expire' => 60,
    ],

];
