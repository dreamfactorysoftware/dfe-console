<?php
namespace DreamFactory\Enterprise\Common\Enums;

use DreamFactory\Library\Utility\Enums\FactoryEnum;

/**
 * Defaults for the operations/runtime environment of DSP/DFE
 */
class EnterpriseDefaults extends FactoryEnum
{
    //*************************************************************************
    //* Defaults
    //*************************************************************************

    /**
     * @type string
     */
    const BOOTSTRAP_FILE = 'bootstrap.config.php';
    /**
     * @var string
     */
    const DFE_ENDPOINT = 'http://cerberus.fabric.dreamfactory.com/api';
    /**
     * @var string
     */
    const DFE_AUTH_ENDPOINT = 'http://cerberus.fabric.dreamfactory.com/api/instance/credentials';
    /**
     * @var string
     */
    const OASYS_PROVIDER_ENDPOINT = 'http://oasys.cloud.dreamfactory.com/oauth/providerCredentials';
    /**
     * @var string
     */
    const INSTANCE_CONFIG_FILE_NAME_PATTERN = '/instance.json';
    /**
     * @var string
     */
    const DB_CONFIG_FILE_NAME_PATTERN = '/{instance_name}.database.config.php';
    /**
     * @var string
     */
    const PLATFORM_VIRTUAL_SUBDOMAIN = '.cloud.dreamfactory.com';
    /**
     * @type string
     */
    const INSTALL_ROOT_MARKER = '/.dreamfactory.php';
    /**
     * @type string
     */
    const COMPOSER_MARKER = '/vendor/autoload.php';
    /**
     * @var string
     */
    const FABRIC_MARKER = '/var/www/.fabric_hosted';
    /**
     * @var string
     */
    const ENTERPRISE_MARKER = '/var/www/.dfe_hosted';
    /**
     * @var string
     */
    const DEFAULT_DOC_ROOT = '/var/www/launchpad/web';
    /**
     * @var string
     */
    const DEFAULT_DEV_DOC_ROOT = '/opt/dreamfactory/dsp/dsp-core/web';
    /**
     * @var string
     */
    const MAINTENANCE_MARKER = '/var/www/.dfe_maintenance';
    /**
     * @var string
     */
    const MAINTENANCE_URI = '/static/dreamfactory/maintenance.php';
    /**
     * @var string
     */
    const UNAVAILABLE_URI = '/static/dreamfactory/unavailable.php';
    /**
     * @var int
     */
    const EXPIRATION_THRESHOLD = 30;
    /**
     * @var string Public storage cookie key
     */
    const PUBLIC_STORAGE_COOKIE = 'dfe.storage_id';
    /**
     * @var string Private storage cookie key
     */
    const PRIVATE_STORAGE_COOKIE = 'dfe.private_storage_id';
    /**
     * @type string
     */
    const DEFAULT_ENVIRONMENT_CLASS = '\\DreamFactory\\Library\\Utility\\Environment';
    /**
     * @type string
     */
    const DEFAULT_RESOLVER_CLASS = '\\DreamFactory\\Library\\Enterprise\\Storage\\Resolver';
    /** @type string The default hash algorithm to use for creating structure */
    const DEFAULT_DATA_STORAGE_HASH = 'sha256';
}
