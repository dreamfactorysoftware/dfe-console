<?php
/**
 * web.php
 * This file is the main configuration file for web side
 *
 * @link   http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 *
 * @var array  $_dbConfig
 * @var array  $_cacheConfig
 * @var string $_repoTag
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
return array(
    //.........................................................................
    //. Basics
    //.........................................................................

    'basePath'          => $_basePath . '/src/app',
    'name'              => $_appName,
    'runtimePath'       => $_logFilePath,
    'defaultController' => $_defaultController,
    //	Preload 'log' component
    'preload'           => array('log'),
    //.........................................................................
    //. Imports
    //.........................................................................

    'import'            => array(
        //	System...
        'application.models.*',
        'application.models.forms.*',
        'application.components.*',
        'application.controllers.*',
        //	Application
        'Cerberus.Yii.Models.*',
        'Cerberus.Yii.Models.Auth.*',
        'Cerberus.Yii.Models.Deploy.*',
        'Cerberus.Yii.Modules.*',
        'Cerberus.Yii.Controllers.*',
        //  Extensions
    ),
    //.........................................................................
    //. Controller Mappings
    //.........................................................................

    'controllerMap'     => array(
        'oauth'     => 'OAuthController',
        'dashboard' => 'Cerberus\\Yii\\Controllers\\DashboardController',
    ),
    //.........................................................................
    //. Modules
    //.........................................................................

    'modules'           => array(
        //	Gii
        //		'gii'   => array(
        //			'class'          => 'system.gii.GiiModule',
        //			'generatorPaths' => array(
        //				'application.gii',
        //			),
        //			'password'       => 'gii',
        //			'ipFilters'      => array(
        //				'*',
        //			),
        //		),
        //	Admin module
        'admin' => array(
            //	Dotted...
            'class' => 'Cerberus.Yii.Modules.Admin.AdminModule',
        ),
        //	API module
        'api'   => array(
            //	Or slashed, it'll work either way...
            'class' => 'Cerberus\\Yii\\Modules\\Api\\ApiModule',
        ),
    ),
    //.........................................................................
    //. Components
    //.........................................................................

    'components'        => array_merge(

    //	our local config...
        array(
            'assetManager'     => array(
                'class'      => 'CAssetManager',
                'basePath'   => 'assets',
                'baseUrl'    => '/assets',
                'linkAssets' => true,
            ),
            'authManager'      => array(
                'class'        => 'CDbAuthManager',
                'connectionID' => 'db',
            ),
            'clientScript'     => array(
                'scriptMap' => array(
                    //	Don't load any jQuery. We get it from the CDN.
                    'jquery.js'       => false,
                    'jquery.min.js'   => false,
                    'jqueryui.js'     => false,
                    'jqueryui.min.js' => false,
                ),
            ),
            'errorHandler'     => array(
                //	Set to a relative URL, each controller must supply actionError()
                'errorAction' => 'web/error',
            ),
            'urlManager'       => array(
                'urlFormat'      => 'path',
                'showScriptName' => false,
                'rules'          => array(),
            ),
            'user'             => array(
                // enable cookie-based authentication
                'allowAutoLogin' => true,
                'loginUrl'       => array($_defaultController . '/login'),
            ),
            //.........................................................................
            //. Database Configurations. One connection per file
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
            //	Cache
            'db.cache'         => require( __DIR__ . '/database.fabric_auth.config.php' ),
            //	Cache
            'db.drupal'        => require( __DIR__ . '/database.drupal.config.php' ),
            //	db-east-1
            'db.db-east-1'     => require( __DIR__ . '/database.db-east-1.config.php' ),
            //.........................................................................
            //. Logging
            //.........................................................................

            'log'              => array(
                'class'  => 'CLogRouter',
                'routes' => array(
                    array(
                        'class'              => 'DreamFactory\\Yii\\Logging\\LiveLogRoute',
                        'maxFileSize'        => '102400',
                        'logFile'            => basename( \Kisma::get( 'app.log_file' ) ),
                        'logPath'            => $_logFilePath,
                        'excludedCategories' => array(
                            'system.CModule',
                            'system.base.CModule',
                            'system.db.CDbConnection',
                            'system.db.CDbCommand',
                            '/^system.db.ar.(.*)+$/',
                            'system.web.filters.CFilterChain',
                        ),
                    ),
                ),
            ),
        ),
        //.........................................................................
        //. Database Cache
        //.........................................................................

        $_dbCache
    ),
    //.........................................................................
    //. Application Parameters
    //.........................................................................

    'params'            => $_commonConfig,
);
