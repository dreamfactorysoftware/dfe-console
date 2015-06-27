<?php
//******************************************************************************
//* The instance provisioners available from this console
//******************************************************************************
use DreamFactory\Enterprise\Common\Enums\PortableTypes;

return [
    //  The default provisioner
    'default' => 'rave',
    //  The provisioners, or "hosts" of our instances, or "guests".
    'hosts'   => [
        /** RAVE === DSP2 */
        'rave' => [
            /** Our namespace */
            'namespace' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave',
            /** The list of sub-provisioners that make up this provisioner */
            'provides'  => [
                /********************************************************************************
                 * Each provisioner has a main "instance" provisioning class. In addition there
                 * are two sub-provisioners required.
                 *
                 * The first is "storage" which is the class responsible for instance storage
                 * provisioning. The second is "db", or the class/service responsible for instance
                 * database provisioning. Currently, all three are required. However, though the
                 * methods are required to exist by the contact, they may have empty method bodies.
                 *
                 * In addition, any of these "resource" provisioners may implement the "Portability"
                 * interface making them available for import/export services from the console and
                 * dashboard. The "rave" provisioner does this and offers import/export services
                 * through the provisioning sub-system.
                 *
                 * Developers may add additional sub-provisioners to the list in their own
                 * provisioner host class.
                 ********************************************************************************/
                PortableTypes::INSTANCE => 'InstanceProvisioner',
                PortableTypes::STORAGE  => 'StorageProvisioner',
                PortableTypes::DATABASE => 'DatabaseProvisioner',
            ],
            'instance'  => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\Provisioner',
            'storage'   => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\StorageProvisioner',
            'db'        => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave\\DatabaseProvisioner',
            /********************************************************************************
             * Provisioners may provide "offerings" or options that dictate certain features of
             * the available guest(s). Selecting a version for instance (as below). It can be
             * used for anything and provides an automatic UI in the Dashboard for user selection.
             ********************************************************************************/
            /** Our offerings */
            'offerings' => [
                //  An "id" for this offering
                'instance-version' => [
                    //  The display (label) name to show on the UI
                    'name'       => 'Version',
                    //  Any text you wish displayed below the selection (i.e. help text, explanation, etc.)
                    'help-block' => 'If you wish, you may choose a different version of the DSP to provision.',
                    //  The item in the below list of items to pre-select for the user.
                    'suggested'  => '1.10.x-dev',
                    //  The list of items to show for this offering.
                    'items'      => [
                        '1.10.x-dev' => [
                            'document-root' => '/var/www/_releases/dsp-core/1.10.x-dev/web',
                            'description'   => 'DSP v1.10.x-dev',
                        ],
                    ],
                ],
            ],
        ],
    ],
];