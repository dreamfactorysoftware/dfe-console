<?php
//******************************************************************************
//* General DFE Console Settings
//******************************************************************************

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Enums\ServerTypes;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Services\Auditing\Enums\AuditMessageFormats;

return [
    //******************************************************************************
    //* Licensing
    //******************************************************************************
    //  The installation key given to you by DreamFactory sales
    'install-key'              => env('DFE_INSTALL_KEY'),
    //******************************************************************************
    //* General
    //******************************************************************************
    //  The id of THIS cluster
    'cluster-id'               => env('DFE_CLUSTER_ID'),
    //  A string to be pre-pended to instance names for non-admin users
    'instance-prefix'          => env('DFE_DEFAULT_INSTANCE_PREFIX'),
    //  The hash algorithm for hashing api keys. Defaults to 'sha256'
    'signature-method'         => env('DFE_SIGNATURE_METHOD', EnterpriseDefaults::DEFAULT_SIGNATURE_METHOD),
    //  The list of allowed partners
    'allowed-partners'         => [env('DFE_PARTNER_ID', 'dfe'), 'hs', 'df',],
    'default-domain'           => env('DFE_DEFAULT_DOMAIN', '.dreamfactory.com'),
    'default-domain-protocol'  => env('DFE_DEFAULT_DOMAIN_PROTOCOL', 'https'),
    //  Set this to FALSE to disallow contact with this console via the Ops API
    'enable-console-api'       => true,
    //  The default number of days to keep metrics data
    'metrics-keep-days'        => env('DFE_METRICS_DAYS_TO_KEEP', ConsoleDefaults::DEFAULT_METRICS_DAYS_TO_KEEP),
    'metrics-detail-keep-days' => env('DFE_METRICS_DETAIL_DAYS_TO_KEEP', ConsoleDefaults::DEFAULT_METRICS_DETAIL_DAYS_TO_KEEP),
    //  The url to download the current version of DFE console
    'dist-update-url'          => 'https://github.com/dreamfactorysoftware/dfe-console/archive/master.zip',
    //  The dashboard URL
    'dashboard-url'            => env('DFE_DASHBOARD_URL'),
    //  The support email
    'support-email-address'    => env('DFE_SUPPORT_EMAIL_ADDRESS', 'support@dreamfactory.com'),
    /** Enable/disable the fast-track "one-click" pipeline */
    'enable-fast-track'        => env('DFE_ENABLE_FAST_TRACK', false),
    /** The route to use for the FastTrack auto-registration "one-click" pipeline */
    'fast-track-route'         => '/fast-track',
    /** Only allow HubSpot landing pages to use FastTrack */
    'fast-track-hubspot-only'  => false,
    /** The string to search for during instance initialization to indicate success */
    'fast-track-admin-html'    => 'Create a System Admin User',
    /** When making web requests, the User-Agent to usef */
    'user-agent'               => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36',
    //*****************************************************************************
    //* Auditing details for instances
    //******************************************************************************
    'audit'                    => [
        //  For audit messages
        'host'           => env('DFE_AUDIT_HOST'),
        'port'           => env('DFE_AUDIT_PORT'),
        'message-format' => env('DFE_AUDIT_MESSAGE_FORMAT', AuditMessageFormats::GELF),
        //  For client presentation
        'client-host'    => env('DFE_AUDIT_CLIENT_HOST', env('DFE_AUDIT_HOST')),
        'client-port'    => env('DFE_AUDIT_CLIENT_PORT'),
    ],
    //******************************************************************************
    //* Blueprint Repository
    //******************************************************************************
    'blueprints'               => [
        'path' => env('DFE_BLUEPRINT_PATH', ConsoleDefaults::DEFAULT_BLUEPRINT_REPO_PATH),
        'vcs'  => true,
    ],
    //******************************************************************************
    //* Common settings across portions of app
    //******************************************************************************
    'common'                   => [
        'display-name'       => 'DreamFactory™ Enterprise Console',
        'display-version'    => env('DFE_VERSION', '1.0.26'),
        'display-copyright'  => '© DreamFactory Software, Inc. 2012-' . date('Y') . '. All Rights Reserved.',
        /**
         * Theme selection -- a bootswatch theme name
         * Included are cerulean, darkly, flatly, paper, and superhero.
         * You may also install other compatible themes and use them as well.
         */
        'themes'             => ['auth' => 'darkly', 'page' => 'flatly'],
        /** Auth pages 256x256px image */
        'login-splash-image' => env('DFE_LOGIN_SPLASH_IMAGE', '/vendor/dfe-common/img/logo-dfe.png'),
        /**  NavBar 194x50px image. Shown on top of inner pages. */
        'navbar-image'       => env('DFE_NAVBAR_IMAGE', '/img/logo-navbar-194x42.png'),
        /** Custom css to load */
        'custom-css-file'    => env('DFE_CUSTOM_CSS_FILE'),
    ],
    //******************************************************************************
    //* General instance settings
    //******************************************************************************
    'instance'                 => [
        /** Settings for dfe-api-client */
        'api'                  => [
            /**
             * If true, admin credentials are required for instance API use.
             * By default, the console utilizes the default provisioning channel
             * to obtain metrics and resource information.
             *
             * **This setting trumps the "api-key" setting which follows**
             */
            'login-required' => env('DFE_INSTANCE_API_LOGIN_REQUIRED', false),
            /** An API key to use for communications instead of provisioning channel */
            'api-key'        => env('DFE_INSTANCE_API_KEY'),
            /** The header to use when transmitting the API key. Defaults to "X-DreamFactory-API-Key" */
            'api-key-header' => env('DFE_INSTANCE_API_HEADER', EnterpriseDefaults::INSTANCE_API_HEADER),
        ],
        /** The resource URI from which to pull resource information. Currently, only "environment" is supported. */
        'metrics-resource-uri' => 'environment',
    ],
    //******************************************************************************
    //* UI Settings
    //******************************************************************************
    'ui'                       => [
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
    'security'                 => [
        'console-api-url'           => env('DFE_CONSOLE_API_URL'),
        /** This key needs to match the key configured in the dashboard */
        'console-api-key'           => env('DFE_CONSOLE_API_KEY'),
        'console-api-client-id'     => env('DFE_CONSOLE_API_CLIENT_ID'),
        'console-api-client-secret' => env('DFE_CONSOLE_API_CLIENT_SECRET'),
    ],
];
