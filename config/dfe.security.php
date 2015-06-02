<?php

//******************************************************************************
//* DFE Console security settings
//******************************************************************************

return [
    //  If true, users may self-register. Otherwise, admins must create users */
    'open-registration'         => false,
    //******************************************************************************
    //* Console API Keys
    //******************************************************************************
    'console-api-url'           => env( 'DFE_CONSOLE_API_URL', 'http://localhost/api/v1/ops/' ),
    /** This key needs to match the key configured in the dashboard */
    'console-api-key'           => env( 'DFE_CONSOLE_API_KEY', '%]3,]~&t,EOxL30[wKw3auju:[+L>eYEVWEP,@3n79Qy' ),
    'console-api-client-id'     => env( 'DFE_CONSOLE_API_CLIENT_ID' ),
    'console-api-client-secret' => env( 'DFE_CONSOLE_API_CLIENT_SECRET' ),
];