<?php namespace DreamFactory\Enterprise\Console\Enums;

class ConsoleDefaults
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string The name of the private path */
    const PRIVATE_PATH_NAME = '.private';
    /** @type string The name of the snapshots path */
    const SNAPSHOT_PATH_NAME = 'snapshots';
    /** @type string The default prefix for outgoing email */
    const EMAIL_SUBJECT_PREFIX = '[DFE]';
    /** @type string The default hash algorithm used for signing requests */
    const SIGNATURE_METHOD = 'sha256';
    /** @type string The default export/mount point/directory where instance data lives */
    const STORAGE_ROOT = '/data/storage';
    /** @type string The name of the cluster environment file */
    const CLUSTER_ENV_FILE = '.env.cluster.json';
    /** @type string The value to put in the image name field for hosted instances */
    const DFE_CLUSTER_BASE_IMAGE = 'dfe.standard';
}
