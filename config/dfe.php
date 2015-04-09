<?php
//******************************************************************************
//* DFE Console Specific Settings
//******************************************************************************

use DreamFactory\Library\Fabric\Database\Enums\GuestLocations;

return [
    /** This key needs to match the key configured in the dashboard */
    'client-hash-key' => '%]3,]~&t,EOxL30[wKw3auju:[+L>eYEVWEP,@3n79Qy',
    'provisioning'    => [
        //  Storage & storage layout options/settings
        'storage-zone-type'           => 'static',          //  Either "static" or "dynamic"
        'static-zone-name'            => 'ec2.us-east-1a',  //  The "static" storage zone
        'public-path-base'            => '/',               //  relative to storage path (hosted or non)
        'private-path-base'           => '.private',        //  relative to storage path (hosted or non)
        'snapshot-path'               => 'snapshots',       // relative to owner-private-path
        'public-paths'                => ['applications', 'plugins', 'vendor', '.private',],
        'private-paths'               => ['.cache', 'config', 'scripts', 'scripts.user',],
        'owner-private-paths'         => ['snapshots',],
        //  Instance provisioning defaults
        'default-cluster-id'          => env( 'DFE_DEFAULT_CLUSTER', 'cluster-east-1' ),
        'default-db-server-id'        => env( 'DFE_DEFAULT_DATABASE', 'db-east-1' ),
        'default-guest-location'      => env( 'DFE_DEFAULT_GUEST_LOCATION', GuestLocations::DFE_CLUSTER ),
        'default-ram-size'            => env( 'DFE_DEFAULT_RAM_SIZE', 1 ),
        'default-disk-size'           => env( 'DFE_DEFAULT_DISK_SIZE', 8 ),
        //  Notification settings
        'email-subject-prefix'        => '[DFE]',
        //  Instance defaults
        //@todo update image to 14.* LTS x64
        'default-vendor-image-id'     => 4647,              //	Ubuntu server 12.04.1 i386
        'default-vendor-image-flavor' => 0,                 //	i386
        'default-dns-zone'            => env( 'DFE_DEFAULT_ZONE', 'cloud' ),
        'default-dns-domain'          => env( 'DFE_DEFAULT_DOMAIN', 'dreamfactory.com' ),
        //  Disallowed instance names
        'forbidden-names'             => [
            'dreamfactory',
            'dream',
            'factory',
            'developer',
            'wiki',
            'cloud',
            'www',
            'fabric',
            'api',
            'db',
            'database',
            'dsp',
            'dfe',
            'dfac',
            'df',
            'dfab',
            'dfdsp',
        ],
    ],
];