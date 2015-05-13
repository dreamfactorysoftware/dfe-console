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
            /** Our sub-provisioners */
            'instance'  => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\Provisioner',
            'storage'   => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\StorageProvisioner',
            'db'        => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\DatabaseProvisioner',
            /** Our offerings */
            'offerings' => [
                'instance-version' => [
                    'name'        => 'Instance Version',
                    'description' => null,
                    'suggested'   => '1.9.x-dev',
                    'items'       => [
                        '1.9.2'     => ['document-root' => '/var/www/_releases/dsp-core/1.9.2/web', 'description' => '1.9.2',],
                        '1.9.x-dev' => ['document-root' => '/var/www/_releases/dsp-core/1.9.x-dev/web', 'description' => '1.9.2',],
                    ],
                ],
            ],
        ],
    ],
];