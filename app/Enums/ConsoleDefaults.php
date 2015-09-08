<?php namespace DreamFactory\Enterprise\Console\Enums;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;

/**
 * DFE console default constants
 *
 * @package DreamFactory\Enterprise\Console\Enums
 */
class ConsoleDefaults
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string The name of the private path */
    const PRIVATE_PATH_NAME = EnterpriseDefaults::PRIVATE_PATH_NAME;
    /** @type string The name of the snapshots path */
    const SNAPSHOT_PATH_NAME = EnterpriseDefaults::SNAPSHOT_PATH_NAME;
    /** @type string The default prefix for outgoing email */
    const EMAIL_SUBJECT_PREFIX = EnterpriseDefaults::EMAIL_SUBJECT_PREFIX;
    /** @type string The default hash algorithm used for signing requests */
    const SIGNATURE_METHOD = EnterpriseDefaults::SIGNATURE_METHOD;
    /** @type string The default export/mount point/directory where instance data lives */
    const STORAGE_ROOT = EnterpriseDefaults::STORAGE_ROOT;
    /** @type string The value to put in the image name field for hosted instances */
    const DFE_CLUSTER_BASE_IMAGE = EnterpriseDefaults::DFE_CLUSTER_BASE_IMAGE;
    /**
     * @type string
     */
    const DEFAULT_HANDLER_NAMESPACE = EnterpriseDefaults::DEFAULT_HANDLER_NAMESPACE;
    /** @type string The name of the cluster manifest file */
    const CLUSTER_MANIFEST_FILE = EnterpriseDefaults::CLUSTER_MANIFEST_FILE;
    /**
     * @type string The type of compression to use when making exports
     */
    const DEFAULT_DATA_COMPRESSOR = EnterpriseDefaults::DEFAULT_DATA_COMPRESSOR;
    /**
     * @type string The default required storage paths
     */
    const DEFAULT_REQUIRED_STORAGE_PATHS = EnterpriseDefaults::DEFAULT_REQUIRED_STORAGE_PATHS;
    /**
     * @type string The default required private paths
     */
    const DEFAULT_REQUIRED_PRIVATE_PATHS = EnterpriseDefaults::DEFAULT_REQUIRED_PRIVATE_PATHS;
    /**
     * @type string The default required private paths
     */
    const DEFAULT_REQUIRED_OWNER_PRIVATE_PATHS = EnterpriseDefaults::DEFAULT_REQUIRED_OWNER_PRIVATE_PATHS;
    /**
     * @type string
     */
    const UI_PREFIX = 'v1';
    /**
     * @type string
     */
    const DEFAULT_DOMAIN_PROTOCOL = EnterpriseDefaults::DEFAULT_DOMAIN_PROTOCOL;
    /**
     * @type int The default number of days to keep system metrics
     */
    const DEFAULT_METRICS_DAYS_TO_KEEP = EnterpriseDefaults::DEFAULT_METRICS_DAYS_TO_KEEP;
}
