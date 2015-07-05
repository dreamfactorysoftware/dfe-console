<?php
//******************************************************************************
//* DFE Console session configuration
//******************************************************************************
return [
    //  Default Session Driver
    'driver'          => env('SESSION_DRIVER', 'file'),
    //  Session Lifetime
    'lifetime'        => 120,
    'expire_on_close' => false,
    //  Session Encryption
    'encrypt'         => false,
    //  Session File Location
    'files'           => realpath(base_path('bootstrap/cache/sessions')),
    //  Session Database Connection
    'connection'      => null,
    //  Session Database Table
    'table'           => 'session_t',
    //  Session Sweeping Lottery
    'lottery'         => [2, 100],
    //  Session Cookie Name
    'cookie'          => 'dfe-console',
    //  Session Cookie Path
    'path'            => '/',
    //  Session Cookie Domain
    'domain'          => null,
    //  HTTPS Only Cookies
    'secure'          => false,
];
