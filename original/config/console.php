<?php
/**
 * console.php
 * This file contains the configuration information for running background tasks
 *
 * @link   http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 *
 * @var array  $_dbConfig
 * @var string $_appTag
 * @var string $_appName
 */
/**
 * @var string
 */
const DSP_VERSION = '1.8.1';

/**
 * Load up the common configuration between the web and background apps,
 * setting globals whilst at it.
 */
$_commonConfig = require( __DIR__ . '/common.config.php' );

/**
 * This is the main Yii configuration array that is returned
 * comprising of all the separate parts that get pulled in.
 */
//*************************************************************************
//* Global Configuration Variables
//*************************************************************************
return array(
    //.........................................................................
    //. Basics
    //.........................................................................

    'basePath'    => $_basePath . '/src/app',
    'name'        => $_appName,
    'runtimePath' => $_logFilePath,
    'preload'     => array('log', 'db'),
    //.........................................................................
    //. Command Mapping
    //.........................................................................

    'commandMap'  => array(
        'vendorStateSweep' => array(
            'class' => 'Cerberus\\Commands\\VendorStateSweepCommand',
        ),
        'processQueue'     => array(
            'class' => 'Cerberus\\Commands\\ProcessQueueCommand',
        ),
        'vendorRefresh'    => array(
            'class' => 'Cerberus\\Commands\\VendorRefreshCommand',
        ),
        'dns'              => array(
            'class' => 'Cerberus\\Commands\\ManageDnsCommand',
        ),
        'janitor'          => array(
            'class' => 'Cerberus\\Commands\\JanitorCommand',
        ),
    ),
    //.........................................................................
    //. Default Imports
    //.........................................................................

    'import'      => array(
        //	System...
        'application.models.*',
        'application.components.*',
        'application.controllers.*',
    ),
    //.........................................................................
    //. Modules
    //.........................................................................

    'modules'     => array(
        //	API module
        'api' => array(
            'class' => 'Cerberus.Yii.Modules.Api.ApiModule',
        ),
    ),
    //.........................................................................
    //. Components
    //.........................................................................

    //	application components
    'components'  => array_merge(

        array(
            'log'              => array(
                'class'  => 'CLogRouter',
                'routes' => array(
                    array(
                        'class'              => 'DreamFactory\\Yii\\Logging\\LiveLogRoute',
                        'levels'             => '',
                        'maxFileSize'        => '102400',
                        'logFile'            => basename( \Kisma::get( 'app.log_file' ) ),
                        'logPath'            => $_logFilePath,
                        'excludedCategories' => array(
                            'system.CModule',
                            'system.db.CDbConnection',
                            'system.db.CDbCommand',
                            '/^system.db.ar.(.*)+$/',
                            'system.web.filters.CFilterChain',
                        ),
                    ),
                ),
            ),
            //.........................................................................
            //. Database
            //.........................................................................

            //	Database (Site/Main)
            'db'               => require( __DIR__ . '/database.fabric_auth.config.php' ),
            //	Auth Database
            'db.fabric_auth'   => require( __DIR__ . '/database.fabric_auth.config.php' ),
            //	Deployment Database
            'db.fabric_deploy' => require( __DIR__ . '/database.fabric_deploy.config.php' ),
            //	Developer site
            'db.developer'     => require( __DIR__ . '/database.developer_site.config.php' ),
            //	Fabric Host Master
            'db.cumulus'       => require( __DIR__ . '/database.cumulus.fabric.config.php' ),
            //	db-east-1
            'db.db-east-1'     => require( __DIR__ . '/database.db-east-1.config.php' ),
            //	Cache
            'db.cache'         => require( __DIR__ . '/database.fabric_auth.config.php' ),
            //	Drupal
            'db.drupal'        => require( __DIR__ . '/database.drupal.config.php' ),
        ),
        /**
         * The database cache, if available
         */
        !empty( $_dbCache ) ? $_dbCache : array()
    ),
    /**
     * Application-level Parameters
     * Use {@see Pii::getParam($name,$defaultValue)} to retrieve.
     */
    'params'      => $_commonConfig,
);
