<?php
/**
 * This file was part of the DreamFactory Services Platform(tm) (DSP)
 *
 * DreamFactory Services Platform(tm) <http://github.com/dreamfactorysoftware/dsp-core>
 * Copyright 2012-2013 DreamFactory Software, Inc. <developer-support@dreamfactory.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Cerberus\Yii\Utility;

use CException;
use Composer\Autoload\ClassLoader;
use Kisma\Core\Enums\CoreSettings;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;
use Monolog\Logger;
use Yii;

/**
 * Pii
 * A Yii helper
 *
 * @method static string encode( $text ) Encodes special characters into HTML entities.
 * @method static string listData( $models, $textColumn, $idColumn ) Makes dropdown data arrays
 */
class Pii extends \Yii
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const FABRIC_MARKER = '/var/www/.fabric_hosted';
	/**
	 * @var string
	 */
	const DEFAULT_DOC_ROOT = '/var/www/launchpad/web';
	/**
	 * @var bool If true, the configuration (web.php) and it's subs will be cached for the session if not in DEBUG mode
	 */
	const ENABLE_CONFIG_CACHE = true;
	/**
	 * @var bool If true, the configuration (web.php) will be cached regardless of debug settings.
	 */
	const FORCE_CONFIG_CACHE = false;
	/**
	 * @var bool
	 */
	const USE_YII_REGISTER_AUTOLOAD = true;

	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	 * @var Logger
	 */
	protected static $_logger = null;
	/**
	 * @var \CHttpRequest Cache the current request
	 */
	protected static $_thisRequest = null;
	/**
	 * @var \CClientScript Cache the client script object for speed
	 */
	protected static $_clientScript = null;
	/**
	 * @var \CWebUser Cache the user object for speed
	 */
	protected static $_thisUser = null;
	/**
	 * @var \CController Cache the current controller for speed
	 */
	protected static $_thisController = null;
	/**
	 * @var \CAttributeCollection Cache the application parameters for speed
	 */
	protected static $_appParameters = null;
	/**
	 * @var array An array of class names to search in for missing methods
	 */
	protected static $_classPath = array();

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Bootstraps the Yii application, setting all the necessary junk
	 *
	 * @param string                         $docRoot           The document root of the web site
	 * @param \Composer\Autoload\ClassLoader $autoloader        The autoloader returned by composer
	 * @param string                         $className         The name of the CApplication class to run
	 * @param string                         $config            A configuration array or the name of the configuration file
	 * @param bool                           $autoRun           If true, the app class is created and ran via CApplication::run()
	 * @param bool                           $prependAutoloader If true, the Composer autoloader will be prepended to the SPL list...
	 * @param bool                           $enableConfigCache If true, the configuration will be cached for speed
	 *
	 * @return \CConsoleApplication|\CWebApplication
	 */
	public static function run( $docRoot, $autoloader, $className = null, $config = null, $autoRun = true, $prependAutoloader = true, $enableConfigCache = true )
	{
		$_basePath = dirname( $docRoot );

		$_appMode = ( 'cli' == PHP_SAPI ? 'console' : 'web' );
		$_configPath = $_basePath . '/config';
		$_configFile = $_configPath . '/' . $_appMode . '.php';
		$_logPath = $_basePath . '/log';

		$_hostName = static::_determineHostName();

		$_logName = $_appMode . '.' . $_hostName . '.log';
		$_logFile = $_logPath . '/' . $_logName;

		//	And our log
		Log::setDefaultLog( $_logFile );

		//	Load constants...
		/** @noinspection PhpIncludeInspection */
		if ( file_exists( $_configPath . '/constants.config.php' ) )
		{
			/** @noinspection PhpIncludeInspection */
			if ( false === @include( $_configPath . '/constants.config.php' ) )
			{
				Log::error( 'Error loading system constants.' );
			}
		}

		//	Create an alias for our configuration directory
		static::alias( 'application.config', $_configPath );
		static::alias( 'application.log', $_logPath );

		//	Load up any other aliases
		if ( file_exists( $_configPath . '/aliases.config.php' ) )
		{
			/** @noinspection PhpIncludeInspection */
			@include_once( $_configPath . '/aliases.config.php' );
		}

		//	App settings
		\Kisma::set(
			array(
				'app.app_path'            => $_basePath . '/web',
				'app.config_path'         => $_configPath,
				'app.log_path'            => $_logPath,
				'app.log_file'            => $_logFile,
				'app.template_path'       => $_configPath . '/templates',
				'app.vendor_path'         => $_basePath . '/vendor',
				CoreSettings::AUTO_LOADER => $autoloader,
				'app.app_class'           => $_appClass = $className ? : ( 'cli' == PHP_SAPI ? 'CConsoleApplication' : 'CWebApplication' ),
				'app.config_file'         => $_configPath . '/' . $_appMode . '.php',
				//	Platform settings
				'platform.host_name'      => $_hostName,
				'platform.fabric_hosted'  => $_isFabric = ( class_exists( '\\Fabric', false )
						? \Fabric::fabricHosted() : ( FilterInput::server( 'DOCUMENT_ROOT' ) ==
													  static::DEFAULT_DOC_ROOT && file_exists( static::FABRIC_MARKER ) ) ),
			)
		);

		echo 'loading ' . $_configFile;
		//.........................................................................
		//. App Create & Run...
		//.........................................................................

		//	Copy configuration
		$_config = $config;

		//	Notice the &&, this will allow override of constant
//		$_cacheEnabled = ( static::ENABLE_CONFIG_CACHE && $enableConfigCache );
//
//		//	Load configuration if not specified, or in debug mode
//		if ( empty( $_config ) && $_cacheEnabled )
//		{
//			//	Try and load it from the cache...
//			if ( false === ( $_config = DataCache::load( $_key = $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_HOST'] . '.' . $_appMode ) ) )
//			{
//				//	Not there, stick it in the cache
//				/** @noinspection PhpIncludeInspection */
//				DataCache::store( $_key, $_config = require( $_configFile ) );
//			}
//		}

		//	If I still have no config, read from disk
		if ( empty( $_config ) )
		{
			//	Otherwise just read the configuration
			/** @noinspection PhpIncludeInspection */
			$_config = require( $_configFile );
		}

		//	Register the autoloader cuz Yii clobbers it somehow
		if ( $autoloader )
		{
			static::registerAutoloader(
				array(
					$autoloader,
					'loadClass'
				),
				!$prependAutoloader
			);
		}

		//	Instantiate and run baby!
		static::app( $_app = static::createApplication( $_appClass, $_config ) );

		//	Just return the app if there is one...
		if ( true !== $autoRun )
		{
			return $_app;
		}

		return $_app->run();
	}

	/**
	 * Checks to see if the passed in data is an Url
	 *
	 * @param string $data
	 *
	 * @return boolean
	 */
	public static function isUrl( $data )
	{
		return ( ( @parse_url( $data ) ) ? true : false );
	}

	/**
	 * Checks for an empty variable. Useful because the PHP empty() function cannot be reliably used with overridden __get methods.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function isEmpty( $value )
	{
		return empty( $value );
	}

	//********************************************************************************
	//* Yii Convenience Mappings
	//********************************************************************************

	/**
	 * Shorthand version of Yii::app() with caching. Ya know, for speed!
	 *
	 * @param \CApplication|\CConsoleApplication|\CWebApplication|null $app
	 *
	 * @return \CConsoleApplication|\CWebApplication
	 */
	public static function app( $app = null )
	{
		/** @var $_thisApp \CApplication|\CWebApplication|\CConsoleApplication */
		static $_thisApp = null;

		if ( false === $app || null !== $_thisApp )
		{
			return $_thisApp;
		}

		$_thisApp = $app ? : parent::app();

		//	Non-CLI requests have clientScript and a user maybe
		if ( $_thisApp )
		{
			if ( 'cli' != PHP_SAPI )
			{
				static::$_clientScript = $_thisApp->getComponent( 'clientScript', false );
				static::$_thisUser = $_thisApp->getComponent( 'user', false );
			}

			static::$_thisRequest = $_thisApp->getComponent( 'request', false );
			static::$_appParameters = $_thisApp->getParams();
		}

		return $_thisApp;
	}

	/**
	 * @param string $prefix If specified, only parameters with this prefix will be returned
	 * @param bool   $regex  If true, $prefix will be treated as a regex pattern
	 *
	 * @return array
	 */
	public static function params( $prefix = null, $regex = false )
	{
		if ( empty( static::$_appParameters ) )
		{
			static::$_appParameters = static::app()->getParams();
		}

		if ( null !== $prefix )
		{
			$_parameters = array();

			if ( false === $regex )
			{
				//	Make sure a trailing dot is added to prefix...
				$prefix = trim( strtolower( rtrim( $prefix, ' .' ) . '.' ) );
			}

			foreach ( static::$_appParameters as $_key => $_value )
			{
				if ( false !== $regex )
				{
					if ( 1 != preg_match( $prefix, $_key, $_matches ) )
					{
						continue;
					}

					$_parameters[str_ireplace( $_matches[0], null, $_key )] = $_value;
				}
				elseif ( false !== stripos( $_key, $prefix, 0 ) )
				{
					$_parameters[str_ireplace( $prefix, null, $_key )] = $_value;
				}
			}

			return $_parameters;
		}

		return static::$_appParameters;
	}

	/**
	 * @param string $db
	 *
	 * @return \PDO
	 */
	public static function pdo( $db = 'db' )
	{
		return static::db( $db )->getPdoInstance();
	}

	/**
	 * Shorthand version of Yii::app()->getController()
	 *
	 * @return \CController|\CBaseController
	 */
	public static function controller()
	{
		return static::app()->getController();
	}

	/**
	 * Shorthand version of Yii::app()->getName()
	 *
	 * @param bool $notEncoded
	 *
	 * @return string
	 */
	public static function appName( $notEncoded = false )
	{
		return $notEncoded ? static::app()->name : static::encode( static::app()->name );
	}

	/**
	 * Convenience method returns the current page title
	 *
	 * @see CController::pageTitle
	 * @see CHtml::encode
	 *
	 * @param $notEncoded bool
	 *
	 * @return string
	 */
	public static function pageTitle( $notEncoded = false )
	{
		return $notEncoded ? static::controller()->getPageTitle() : static::encode( static::controller()->getPageTitle() );
	}

	/**
	 * Convenience method Returns the base url of the current app
	 *
	 * @param $absolute bool
	 *
	 * @return string
	 */
	public static function baseUrl( $absolute = false )
	{
		return static::app()->getBaseUrl( $absolute );
	}

	/**
	 * Convenience method Returns the base path of the current app
	 *
	 * @param string $subPath
	 *
	 * @return string
	 */
	public static function basePath( $subPath = null )
	{
		return static::app()->getBasePath() . ( null !== $subPath ? '/' . ltrim( $subPath, '/' ) : null );
	}

	/***
	 * Retrieves and caches the Yii ClientScript object
	 *
	 * @return \CClientScript
	 */
	public static function clientScript()
	{
		return static::app()->getClientScript();
	}

	/**
	 * Terminates the application.
	 * This method replaces PHP's exit() function by calling {@link onEndRequest} before exiting.
	 *
	 * @param integer $status exit status (value 0 means normal exit while other values mean abnormal exit).
	 * @param boolean $exit   whether to exit the current request. This parameter has been available since version 1.1.5. It defaults to true,
	 *                        meaning the PHP's exit() function will be called at the end of this method.
	 */
	public static function end( $status = 0, $exit = true )
	{
		static::app()->end( $status, $exit );
	}

	/**
	 * @param string $id
	 * @param bool   $createIfNull
	 *
	 * @return \CComponent The component, if found
	 */
	public static function component( $id, $createIfNull = true )
	{
		return static::app()->getComponent( $id, $createIfNull );
	}

	/**
	 * @param string $name
	 *
	 * @return \CDbConnection the database connection
	 */
	public static function db( $name = 'db' )
	{
		return static::component( $name );
	}

	/**
	 * Registers a javascript file.
	 *
	 * @internal param $string \URL of the javascript file
	 * @internal param $integer \the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * </ul>
	 *
	 * @param string|array $urlList
	 * @param int          $pagePosition
	 *
	 * @return \CClientScript
	 */
	public static function scriptFile( $urlList, $pagePosition = \CClientScript::POS_HEAD )
	{
		//	Need external library?
		foreach ( Option::clean( $urlList ) as $_url )
		{
			if ( !static::clientScript()->isScriptFileRegistered( $_url ) )
			{
				static::clientScript()->registerScriptFile( $_url, $pagePosition );
			}
		}

		return static::clientScript();
	}

	/**
	 * Registers a CSS file
	 *
	 * @param string $urlList
	 * @param string $media that the CSS file should be applied to. If empty, it means all media types.
	 *
	 * @return \CClientScript|null|string
	 */
	public static function cssFile( $urlList, $media = null )
	{
		foreach ( Option::clean( $urlList ) as $_url )
		{
			if ( !static::clientScript()->isCssFileRegistered( $_url ) )
			{
				static::clientScript()->registerCssFile( $_url, $media );
			}
		}

		return static::clientScript();
	}

	/**
	 * Registers a piece of CSS code.
	 *
	 * @param string ID that uniquely identifies this piece of CSS code
	 * @param string the CSS code
	 * @param string media that the CSS code should be applied to. If empty, it means all media types.
	 *
	 * @return \CClientScript|null
	 * @access public
	 * @static
	 */
	public static function css( $id, $css, $media = null )
	{
		if ( !static::clientScript()->isCssRegistered( $id ) )
		{
			static::clientScript()->registerCss( $id, $css, $media );
		}

		return static::clientScript();
	}

	/**
	 * Registers a piece of javascript code.
	 *
	 * @param string  ID that uniquely identifies this piece of JavaScript code
	 * @param string  the javascript code
	 * @param integer the position of the JavaScript code. Valid values include the following:
	 *                <ul>
	 *                <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 *                <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 *                <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 *                <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	 *                <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	 *                </ul>
	 *
	 * @return \CClientScript|null|string
	 * @access public
	 * @static
	 */
	public static function script( $id, $script, $position = \CClientScript::POS_READY )
	{
		if ( !static::clientScript()->isScriptRegistered( $id ) )
		{
			static::clientScript()->registerScript(
				$id,
				$script,
				$position
			);
		}

		return static::clientScript();
	}

	/**
	 * Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	 *
	 * @param string content attribute of the meta tag
	 * @param string name attribute of the meta tag. If null, the attribute will not be generated
	 * @param string http-equiv attribute of the meta tag. If null, the attribute will not be generated
	 * @param array  other options in name-value pairs (e.g. 'scheme', 'lang')
	 *
	 * @return \CClientScript|null
	 * @access public
	 * @static
	 */
	public static function metaTag( $content, $name = null, $httpEquivalent = null, $attributes = array() )
	{
		static::clientScript()->registerMetaTag( $content, $name, $httpEquivalent, $attributes );

		return static::clientScript();
	}

	/**
	 * Creates a relative URL based on the given controller and action information.
	 *
	 * @param string the URL route. This should be in the format of 'ControllerID/ActionID'.
	 * @param array  additional GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * @param string the token separating name-value pairs in the URL.
	 *
	 * @return string the constructed URL
	 */
	public static function url( $route, $options = array(), $ampersand = '&' )
	{
		return static::app()->createUrl( $route, $options, $ampersand );
	}

	/**
	 * Returns the current request. Equivalent of {@link CApplication::getRequest}
	 *
	 * @see CApplication::getRequest
	 * @return \CHttpRequest
	 */
	public static function request()
	{
		return static::app()->getRequest();
	}

	/**
	 * Returns the current user identity.
	 *
	 * @return \CUserIdentity
	 */
	public static function identity()
	{
		return static::component( 'user', false );
	}

	/**
	 * Returns the current user. Equivalent of {@link CWebApplication::getUser}
	 *
	 * @return \CWebUser
	 */
	public static function user()
	{
		return static::app()->getUser();
	}

	/**
	 * Returns boolean indicating if user is logged in or not
	 *
	 * @return boolean
	 */
	public static function guest()
	{
		return static::user()->getIsGuest();
	}

	/**
	 * Returns application parameters or default value if not found
	 *
	 * @param string $paramName
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public static function getParam( $paramName, $defaultValue = null )
	{
		$_parameters = static::params();

		return Option::get( $_parameters, $paramName, $defaultValue );
	}

	/**
	 * Convenience access to CAssetManager::publish()
	 *
	 * Publishes a file or a directory.
	 * This method will copy the specified asset to a web accessible directory
	 * and return the URL for accessing the published asset.
	 * <ul>
	 * <li>If the asset is a file, its file modification time will be checked
	 * to avoid unnecessary file copying;</li>
	 * <li>If the asset is a directory, all files and subdirectories under it will
	 * be published recursively. Note, in this case the method only checks the
	 * existence of the target directory to avoid repetitive copying.</li>
	 * </ul>
	 *
	 * @param string  the asset (file or directory) to be published
	 * @param boolean whether the published directory should be named as the hashed basename.
	 *                If false, the name will be the hashed dirname of the path being published.
	 *                Defaults to false. Set true if the path being published is shared among
	 *                different extensions.
	 * @param integer level of recursive copying when the asset is a directory.
	 *                Level -1 means publishing all subdirectories and files;
	 *                Level 0 means publishing only the files DIRECTLY under the directory;
	 *                level N means copying those directories that are within N levels.
	 *
	 * @return string an absolute URL to the published asset
	 * @throws CException if the asset to be published does not exist.
	 * @see CAssetManager::publish
	 */
	public static function publishAsset( $path, $hashByName = false, $level = -1 )
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return static::app()->getAssetManager()->publish( $path, $hashByName, $level );
	}

	/**
	 * Performs a redirect. See {@link CHttpRequest::redirect}
	 *
	 * @param string  $url
	 * @param boolean $terminate
	 * @param int     $statusCode
	 *
	 * @see CHttpRequest::redirect
	 */
	public static function redirect( $url, $terminate = true, $statusCode = 302 )
	{
		static::app()->request->redirect( is_array( $url ) ? $url : static::url( $url ), $terminate, $statusCode );
	}

	/**
	 * Returns the details about the error that is currently being handled.
	 * The error is returned in terms of an array, with the following information:
	 * <ul>
	 * <li>code - the HTTP status code (e.g. 403, 500)</li>
	 * <li>type - the error type (e.g. 'CHttpException', 'PHP Error')</li>
	 * <li>message - the error message</li>
	 * <li>file - the name of the PHP script file where the error occurs</li>
	 * <li>line - the line number of the code where the error occurs</li>
	 * <li>trace - the call stack of the error</li>
	 * <li>source - the context source code where the error occurs</li>
	 * </ul>
	 *
	 * @return array the error details. Null if there is no error.
	 */
	public static function currentError()
	{
		$_handler = static::app()->getErrorHandler();

		if ( !empty( $_handler ) )
		{
			return $_handler->getError();
		}

		return null;
	}

	/**
	 * Determine if PHP is running CLI mode or not
	 *
	 * @return boolean True if currently running in CLI
	 */
	public static function cli()
	{
		return ( 'cli' == PHP_SAPI );
	}

	/**
	 * Get or set a path alias. If $path is provided, this acts like a "setter" otherwise a "getter"
	 * Note, this method neither checks the existence of the path nor normalizes the path.
	 *
	 * @param string $alias    alias to the path
	 * @param string $path     the path corresponding to the alias. If this is null, the corresponding
	 *                         path alias will be removed.
	 * @param string $morePath When retrieving an alias, $morePath will be appended to the end
	 *
	 * @return mixed|null|string
	 */
	public static function alias( $alias, $path = null, $morePath = null )
	{
		if ( null !== $path )
		{
			static::setPathOfAlias( $alias, $path );

			return $path;
		}

		$_path = static::getPathOfAlias( $alias );

		if ( null !== $morePath )
		{
			$_path = trim( rtrim( $_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . ltrim( $morePath, DIRECTORY_SEPARATOR ) );
		}

		return $_path;
	}

	/**
	 * @return boolean whether this is POST request.
	 */
	public static function postRequest()
	{
		static $_is = null;

		if ( null === $_is )
		{
			$_is = static::request()->getIsPostRequest();
		}

		return $_is;
	}

	/**
	 * @return boolean whether this is PUT request.
	 */
	public static function putRequest()
	{
		static $_is = null;

		if ( null === $_is )
		{
			$_is = static::request()->getIsPutRequest();
		}

		return $_is;
	}

	/**
	 * @return boolean whether this is DELETE request.
	 */
	public static function deleteRequest()
	{
		static $_is = null;

		if ( null === $_is )
		{
			$_is = static::request()->getIsDeleteRequest();
		}

		return $_is;
	}

	/**
	 * @return boolean whether this is DELETE request.
	 */
	public static function ajaxRequest()
	{
		static $_is = null;

		if ( null === $_is )
		{
			$_is = static::request()->getIsAjaxRequest();
		}

		return $_is;
	}

	/**
	 * Generic array sorter
	 *
	 * To sort a column in descending order, assign 'desc' to the column's value in the defining array:
	 *
	 * $_columnsToSort = array(
	 *    'date' => 'desc',
	 *    'lastName' => 'asc',
	 *    'firstName' => 'asc',
	 * );
	 *
	 * @param array $arrayToSort
	 * @param array $columnsToSort Array of columns in $arrayToSort to sort.
	 *
	 * @return boolean
	 */
	public static function arraySort( &$arrayToSort, $columnsToSort = array() )
	{
		//	Convert to an array
		if ( !empty( $columnsToSort ) && !is_array( $columnsToSort ) )
		{
			$columnsToSort = array( $columnsToSort );
		}

		//	Any fields?
		if ( !empty( $columnsToSort ) )
		{
			return usort(
				$arrayToSort,
				function ( $a, $b ) use ( $columnsToSort )
				{
					$_result = null;

					foreach ( $columnsToSort as $_column => $_order )
					{
						$_order = trim( strtolower( $_order ) );

						if ( is_numeric( $_column ) && !\Kisma\Core\Utility\Scalar::in( $_order, 'asc', 'desc' ) )
						{
							$_column = $_order;
							$_order = null;
						}

						if ( 'desc' == strtolower( $_order ) )
						{
							return strnatcmp( $b[$_column], $a[$_column] );
						}

						return strnatcmp( $a[$_column], $b[$_column] );
					}
				}
			);
		}

		return false;
	}

	/**
	 * Sorts an array by a single column
	 *
	 * @param array  $sourceArray
	 * @param string $column
	 * @param int    $sortDirection
	 *
	 * @return bool
	 */
	public static function array_multisort_column( &$sourceArray, $column, $sortDirection = SORT_ASC )
	{
		$_sortColumn = array();

		foreach ( $sourceArray as $_key => $_row )
		{
			$_sortColumn[$_key] = ( isset( $_row[$column] ) ? $_row[$column] : null );
		}

		return \array_multisort( $_sortColumn, $sortDirection, $sourceArray );
	}

	/**
	 * Serializer that can handle SimpleXmlElement objects
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public static function serialize( $value )
	{
		try
		{
			if ( $value instanceof \SimpleXMLElement )
			{
				return $value->asXML();
			}

			if ( is_object( $value ) )
			{
				return \serialize( $value );
			}
		}
		catch ( \Exception $_ex )
		{
		}

		return $value;
	}

	/**
	 * Unserializer that can handle SimpleXmlElement objects
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public static function unserialize( $value )
	{
		try
		{
			if ( static::serialized( $value ) )
			{
				if ( $value instanceof \SimpleXMLElement )
				{
					return \simplexml_load_string( $value );
				}

				return \unserialize( $value );
			}
		}
		catch ( \Exception $_ex )
		{
		}

		return $value;
	}

	/**
	 * Tests if a value needs unserialization
	 *
	 * @param mixed $value
	 *
	 * @return boolean
	 */
	public static function serialized( $value )
	{
		$_result = @\unserialize( $value );

		return !( false === $_result && $value != \serialize( false ) );
	}

	/**
	 * @param string $name
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public static function getState( $name, $defaultValue = null )
	{
		return static::app()->getUser()->getState( $name, $defaultValue );
	}

	/**
	 * @param string $name
	 * @param mixed  $value The value to store
	 * @param mixed  $defaultValue
	 *
	 * @return \CConsoleApplication|\CWebApplication
	 */
	public static function setState( $name, $value, $defaultValue = null )
	{
		static::app()->getUser()->setState( $name, $value, $defaultValue );

	}

	/**
	 * @param string $name
	 */
	public static function clearState( $name )
	{
		static::app()->getUser()->setState( $name, null, null );
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 *
	 * @param string $key
	 * @param string $message
	 * @param string $defaultValue
	 *
	 * @return \CConsoleApplication|\CWebApplication
	 */
	public static function setFlash( $key, $message = null, $defaultValue = null )
	{
		static::app()->getUser()->setFlash( $key, $message, $defaultValue );
	}

	/**
	 * Gets a stored flash message
	 * A flash message is available only in the current and the next requests.
	 *
	 * @param string  $key
	 * @param mixed   $defaultValue
	 * @param boolean $delete If true, delete this flash message after accessing it.
	 *
	 * @return string
	 */
	public static function getFlash( $key, $defaultValue = null, $delete = true )
	{
		return static::app()->getUser()->getFlash( $key, $defaultValue, $delete );
	}

	/**
	 * @return string
	 */
	protected static function _determineHostName()
	{
		if ( null === ( $_hostName = \Kisma::get( 'app.host_name' ) ) )
		{
			//	Figure out my name
			if ( isset( $_SERVER, $_SERVER['HTTP_HOST'] ) )
			{
				$_parts = explode( '.', $_SERVER['HTTP_HOST'] );

				if ( 4 == count( $_parts ) )
				{
					if ( 'cumulus' == ( $_hostName = $_parts[0] ) )
					{
						$_hostName = null;
					}
				}
			}
		}

		if ( empty( $_hostName ) )
		{
			$_hostName = str_replace( '.dreamfactory.com', null, gethostname() );
		}

		return $_hostName;
	}

	/**
	 * Also handle CHtml statics...
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments )
	{
		if ( method_exists( '\\CHtml', $name ) )
		{
			return call_user_func_array( array( '\\CHtml', $name ), $arguments );
		}
	}

	public static function import( $alias, $forceInclude = false )
	{
		try
		{
			return parent::import( $alias, $forceInclude );
		}
		catch ( Exception $_ex )
		{
			//	See if composer can find it first...
		}
	}

	/**
	 * Logs a message.
	 * Routes all Yii log messages through Monolog
	 *
	 * @param string $msg      message to be logged
	 * @param string $level    level of the message (e.g. 'trace', 'warning', 'error'). It is case-insensitive.
	 * @param string $category category of the message (e.g. 'system.web'). It is case-insensitive.
	 */
	public static function log( $msg, $level = \CLogger::LEVEL_INFO, $category = 'application' )
	{
		static::$_logger = static::$_logger ? : Log::getLogger();

		if ( ( \Kisma::get( CoreSettings::DEBUG ) || YII_DEBUG ) && YII_TRACE_LEVEL > 0 && $level !== \CLogger::LEVEL_PROFILE )
		{
			$_count = 0;
			$_traces = debug_backtrace();

			foreach ( $_traces as $_trace )
			{
				if ( isset( $_trace['file'], $_trace['line'] ) && 0 !== strpos( $_trace['file'], YII_PATH ) )
				{
					$msg .= "\nin " . $_trace['file'] . ' (' . $_trace['line'] . ')';

					if ( ++$_count >= YII_TRACE_LEVEL )
					{
						break;
					}
				}
			}
		}

		Log::log( $msg, $level, $category );
	}
}
