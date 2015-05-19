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
                    'name'       => 'Version',
                    'help-block' => 'If you wish, you may choose a different version of the DSP to provision.',
                    'suggested'  => '1.10.x-dev',
                    'items'      => [
                        '1.10.x-dev' => ['document-root' => '/var/www/_releases/dsp-core/1.9.x-dev/web', 'description' => 'DSP v1.10.x-dev',],
                    ],
                ],
            ],
        ],
    ],
];