<?php
//******************************************************************************
//* General DFE Console Settings
//******************************************************************************

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Enums\ServerTypes;

return [
    //******************************************************************************
    //* Licensing
    //******************************************************************************
    //  The installation key given to you by DreamFactory sales
    'install-key'             => env('DFE_INSTALL_KEY'),
    //******************************************************************************
    //* General
    //******************************************************************************
    //  The id of THIS cluster
    'cluster-id'              => env('DFE_CLUSTER_ID'),
    //  A string to be pre-pended to instance names for non-admin users
    'instance-prefix'         => env('DFE_DEFAULT_INSTANCE_PREFIX'),
    //  The hash algorithm for hashing api keys. Defaults to 'sha256'
    'signature-method'        => env('DFE_SIGNATURE_METHOD', EnterpriseDefaults::DEFAULT_SIGNATURE_METHOD),
    //  The list of allowed partners
    'allowed-partners'        => ['vz', 'hs', 'df',],
    'default-domain'          => env('DFE_DEFAULT_DOMAIN', '.dreamfactory.com'),
    'default-domain-protocol' => env('DFE_DEFAULT_DOMAIN_PROTOCOL', 'https'),
    //  Set this to FALSE to disallow contact with this console via the Ops API
    'enable-console-api'      => true,
    //******************************************************************************
    //* Common settings across portions of app
    //******************************************************************************
    'common'                  => [
        'display-name'      => 'DreamFactory Enterprise&trade; Console',
        'display-version'   => 'v1.0-beta',
        'display-copyright' => '© DreamFactory Software, Inc. 2012-' . date('Y') . '. All Rights Reserved.',
        /**
         * Theme selection -- a bootswatch theme name
         * Included are cerulean, darkly, flatly, paper, and superhero.
         * You may also install other compatible themes and use them as well.
         */
        'themes'            => ['auth' => 'darkly', 'page' => 'flatly'],
    ],
    //******************************************************************************
    //* UI Settings
    //******************************************************************************
    'ui'                      => [
        'prefix'          => 'v1',
        'button-contexts' => [
            ServerTypes::DB  => 'primary',
            ServerTypes::WEB => 'success',
            ServerTypes::APP => 'warning',
        ],
    ],
    //******************************************************************************
    //* Console API Keys
    //******************************************************************************
    'security'                => [
        'console-api-url'           => env('DFE_CONSOLE_API_URL'),
        /** This key needs to match the key configured in the dashboard */
        'console-api-key'           => env('DFE_CONSOLE_API_KEY'),
        'console-api-client-id'     => env('DFE_CONSOLE_API_CLIENT_ID'),
        'console-api-client-secret' => env('DFE_CONSOLE_API_CLIENT_SECRET'),
    ],
];