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
            /********************************************************************************
             * The namespace for our provisioning classes. This is optional and you may specify
             * fully qualified class names in the "provides" section. You cannot mix and match
             * however. If an "namespace" key exists, it will be pre-pended to all provisioner
             * classes.
             ********************************************************************************/
            'namespace' => 'DreamFactory\\Enterprise\\Services\\Provisioners\\Rave',
            /********************************************************************************
             * Each provisioner has a set of "sub-provisioners". The important one is the
             * "instance" provisioner. Also required are two standard sub-provisioners.
             *
             * The first is "storage" which is the class responsible for instance storage
             * provisioning. The second is "db", or the class/service responsible for instance
             * database provisioning. Currently, all three (instance,db,storage) are required.
             * However, even though the methods are required to exist by the contract, they
             * may have empty method bodies and do nothing.
             *
             * Any of these "resource" sub-provisioners may implement the "Portability"
             * interface, making them available for import/export services from the console and
             * dashboard. The "rave" provisioner does this and offers import/export services
             * through the provisioning sub-system.
             *
             * Developers may add additional sub-provisioners to the list in their own
             * provisioner host class.
             ********************************************************************************/
            'provides'  => [
                PortableTypes::INSTANCE => 'InstanceProvisioner',
                PortableTypes::STORAGE  => 'StorageProvisioner',
                PortableTypes::DATABASE => 'DatabaseProvisioner',
            ],
            /********************************************************************************
             * Provisioners may provide "offerings" or options that dictate certain features of
             * the available guest(s). Selecting a version for instance (as below). It can be
             * used for anything and provides an automatic UI in the Dashboard for user selection.
             ********************************************************************************/
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