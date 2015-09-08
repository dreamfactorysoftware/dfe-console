<?php
//******************************************************************************
//* The instance provisioners available from this console
//******************************************************************************
use DreamFactory\Enterprise\Common\Enums\PortableTypes;

return [
    //  The default provisioner
    'default' => 'dreamfactory',
    //  The provisioners, or "hosts" of our instances, or "guests".
    'hosts'   => [
        /** DreamFactory v2.x */
        'dreamfactory' => [
            /********************************************************************************
             * The namespace for our provisioning classes. This is optional and you may specify
             * fully qualified class names in the "provides" section. You cannot mix and match
             * however. If an "namespace" key exists, it will be pre-pended to all provisioner
             * classes.
             ********************************************************************************/
            /** 'namespace'    => 'App\Provisioners\Acme, */
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
             * Any of these "resource" sub-provisioners may implement the "PortableData"
             * interface, making them available for import/export services from the console and
             * dashboard. The "dreamfactory" provisioner does this and offers import/export services
             * through the provisioning sub-system.
             *
             * Developers may add additional sub-provisioners to the list in their own
             * provisioner host class.
             ********************************************************************************/
            'provides'     => [
                PortableTypes::INSTANCE => DreamFactory\Enterprise\Provisioners\DreamFactory\InstanceProvisioner::class,
                PortableTypes::STORAGE  => DreamFactory\Enterprise\Provisioners\DreamFactory\StorageProvisioner::class,
                PortableTypes::DATABASE => DreamFactory\Enterprise\Provisioners\DreamFactory\DatabaseProvisioner::class,
            ],
            /********************************************************************************
             * Provisioners may provide "offerings" or options that dictate certain features of
             * the available guest(s). Selecting a version for instance (as below). It can be
             * used for anything and provides an automatic UI in the Dashboard for user selection.
             ********************************************************************************/
            'offerings'    => [],
            /** The instance-provided resource discovery uri */
            'resource-uri' => '/api/v2/system/',
        ],
    ],
];