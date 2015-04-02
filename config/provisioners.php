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
            'instance' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\Provisioner',
            'storage'  => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\StorageProvisioner',
            'db'       => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\DatabaseProvisioner',
        ],
    ],
];