<?php
/**
 * common.config.php
 * This file contains any application-level parameters that are to be shared between the background and web services
 *
 * @link    http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author  Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */

//*************************************************************************
//* Global Configuration Variables
//*************************************************************************
use DreamFactory\Library\Utility\Includer;

/**
 * @var string
 */
const APP_VERSION = '1.0.0';
/**
 * @type string The file containing the list of whitelisted hosts
 */
const WHITELIST_HOST_CONFIG_PATH = '/whitelist.config.php';

//	The base path of the project, where it's checked out basically
$_basePath = dirname( __DIR__ );
//	The vendor path
$_vendorPath = $_basePath . '/vendor';
//	Set to false to disable database caching
$_dbCacheEnabled = true;
//	The name of the default controller. "site" just sucks
$_defaultController = 'web';
//	The file storing the mailer configuration
$_mailerConfig = array();
//  Where to put logs
$_logFilePath = $_basePath . '/log';

/**
 * Application Paths
 */
\Kisma::set(
    array(
        'app.app_name'    => $_appName = 'Cerberus',
        'app.doc_root'    => $_docRoot = $_basePath . '/web',
        'app.log_path'    => $_logFilePath,
        'app.vendor_path' => $_vendorPath = $_basePath . '/vendor',
        'app.config_path' => $_basePath . '/config',
    )
);

/**
 * Database Caching
 */
$_dbCache = $_dbCacheEnabled ? array(
    'cache' => array(
        'class'                => 'CDbCache',
        'connectionID'         => 'db.cache',
        'cacheTableName'       => '_app_cache_t',
        'autoCreateCacheTable' => true,
    ),
) : null;

$_mailerConfig = Includer::includeIfExists( __DIR__ . '/mailer.config.php', true, true );

//	And return our parameters array...
return array_merge(
    array(
        //*************************************************************************
        //* General
        //*************************************************************************
        'logLevel'                   => 9,
        'company.name'               => 'DreamFactory Software',
        'oauth.salt'                 => 'rW64wRUk6Ocs+5c7JwQ{69U{]MBdIHqmx9Wj,=C%S#cA%+?!cJMbaQ+juMjHeEx[dlSe%h%kcI',
        'auth.salt'                  => 'e838d88106d8ab5f4181b030984f45e5bc008f44941097187663da3d9737999e81ee9c27228a03e9434ffcd2ef57bf3f1e839aa7f4ed9e387bd8001c02ca337d',
        //*************************************************************************
        //* General
        //*************************************************************************
        //	The email template directory
        'app.template_path'          => __DIR__ . '/templates',
        //	The amazon aws config
        'app.amazon_aws_credentials' => __DIR__ . '/amazon.api-user.keys.php',
        //	The vendor path
        'app.vendor_path'            => $_vendorPath,
        //	Cerberus keys
        'app.cerberus.client_id'     => '033156b1869d771eb0b56d3cd5ff3615',
        'app.cerberus.client_secret' => 'a6bf20a6af251bee487d8979d363d84d49a9861792d5fc086c7a12bf7e49f75b',
        //	Drupal endpoints
        'app.drupal.endpoint.token'  => 'https://www.dreamfactory.com/restws/session/token',
        'app.drupal.endpoint.user'   => 'https://restws_ops:restws_user@www.dreamfactory.com/user.json',
        //*************************************************************************
        //* Scripts
        //*************************************************************************
        'script.provision'           => '/home/dfadmin/chef-launchpad/provision-instance.sh',
        'script.deprovision'         => '/home/dfadmin/chef-launchpad/deprovision-instance.sh',
        //******************************************************************************
        //* Whitelisted Hosts
        //******************************************************************************
        /** These are converted to IP addresses in the api */
        'app.api_whitelist'          => Includer::includeIfExists( __DIR__ . WHITELIST_HOST_CONFIG_PATH ),
    ),
    $_mailerConfig
);
