<?php
//******************************************************************************
//* Allowed referrers for partners/remote services
//******************************************************************************
return [
    /** DreamFactory **/
    'df' => [
        'name'      => 'DreamFactory',
        'referrers' => ['dreamfactory.com'],
        'commands'  => ['register'],
    ],
    /** HubSpot **/
    'hs' => [
        'name'      => 'HubSpot',
        'referrers' => ['hubspot.com'],
        'commands'  => ['register'],
    ],
    /** Docomo **/
    'do' => [
        'name'      => 'Docomo',
        'referrers' => ['hubspot.com', 'dreamfactory.com', 'docomo.com',],
        'commands'  => ['register'],
    ],
    /** Verizon **/
    'vz' => [
        'name'      => 'Verizon',
        'referrers' => ['verizon.com', 'hubspot.com', 'dreamfactory.com'],
        'commands'  => ['register'],
    ],
];
