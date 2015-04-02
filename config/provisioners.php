<?php
//******************************************************************************
//* DFE Console Provisioner Settings
//******************************************************************************

return [
    //  The default provisioner
    'default' => 'rave',
    //  The supported provisioners/hosts
    'hosts'   => [
        'rave' => [
            'instance' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\RaveProvisioner',
            'storage'  => 'DreamFactory\\Enterprise\\Services\\Provisioners\\RaveStorageProvisioner',
            'db'       => 'DreamFactory\\Enterprise\\Services\\Provisioners\\RaveDatabaseProvisioner',
        ],
    ],
];