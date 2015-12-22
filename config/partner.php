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
        'brand'     => [
            'logo'              => '/img/partners/docomo-256x256.png',
            'icon'              => '/img/partners/docomo-256x100.png',
            'copyright'         => '&copy; ' . date('Y') . ' Verizon',
            'copyright-minimal' => '&copy; ' . date('Y') . ' Verizon',
            'copy'              => null,
            'copy-minimal'      => null,
        ],
    ],
];
