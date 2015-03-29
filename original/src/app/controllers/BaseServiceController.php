<?php
/**
 * BaseServiceController.php
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
use DreamFactory\Interfaces\FactoryService;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpMethod;
use Kisma\Core\Enums\OutputFormat;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Inflector;
use Kisma\Core\Utility\Log;

/**
 * BaseServiceController
 * Provides base functionality that all integrated services may require.
 */
abstract class BaseServiceController extends \DreamFactory\Yii\Controllers\DreamRestController
{
	//*************************************************************************
	//* Private Members
	//*************************************************************************
	/**
	 * @var FactoryService
	 */
protected $_x;
	/**
	 * The name of the service of this controller
	 *
	 * @var string
	 */
	protected $_serviceName;
	/**
	 * The configuration options for this service
	 *
	 * @var array
	 */
	protected $_serviceConfig;
	/**
	 * Set this to true if your service requires authentication
	 *
	 * @var bool
	 */
	protected $_requireAuthentication = false;
	/**
	 * An array of required parameters. Consider these parameters required
	 * for all calls to your service.
	 *
	 * @var array
	 */
	protected $_requiredParameters = array();
	/**
	 * If true, while inspecting the header for parameters, the $_REQUEST array will
	 * be scanned if an item was not found in the header. Allows a service to accept
	 * parameters in a payload as well as the headers for flexibility.
	 *
	 * @note The prefix is not used when looking in the $_REQUEST array.
	 *
	 * @var bool
	 */
	protected $_checkRequestForParameters = true;
	/**
	 * The prefix for inbound header parameters. This is used to look for inbound
	 * parameters from the HTTP header. It is combined with HTTP to create the key:
	 *
	 *         HTTP_<prefix>_<varName>.
	 *
	 * If this key exists in the $_SERVER array, the corresponding object property
	 * is set to its value at the time of initialization.
	 *
	 * This prefix combined with configurable header parameters offers great flexibility.
	 *
	 *     Example Completed cKeys (using prefix of X_DF_):
	 *
	 *         HTTP_X_DF_USER_NAME
	 *         HTTP_X_DF_CRM_USER_NAME
	 *         HTTP_X_DF_OAUTH_TOKEN
	 *
	 * @var string
	 */
	protected $_headerParameterPrefix = 'X_';
	/**
	 * The parameters (without prefix above) to expect in the header and their
	 * associated properties. By default, this object will look for access_token,
	 * user_name, password, and user_location in inbound requests.
	 *
	 * The distinction between these "parameters" and the "options" below is that
	 * the header parameters could/should be used to convey authentication information, or
	 * to validate the authenticity of the caller/callee/response/request/etc.
	 *
	 * The options, however, are considered "function arguments". You're basically calling
	 * a remote "function" and passing it these "arguments" to use.
	 *
	 * @var array
	 */
	protected $_headerParameters = array();
	/**
	 * A list of options that will be allowed to be set by either a POST or via config file. If you have
	 * customized options, add them to this array in your subclass.
	 *
	 * @var array
	 */
	protected $_configOptions = array();
	/**
	 * @var string
	 */
	protected $_accessToken;
	/**
	 * @var string
	 */
	protected $_userName;
	/**
	 * @var string
	 */
	protected $_password;
	/**
	 * @var string Optional user location parameter. Can be used to specify a specific device on which a client user is registered.
	 */
	protected $_userLocation;
	/**
	 * Any data in this array will be returned to the requester in the format
	 * they have requested.
	 *
	 * @var array
	 */
	protected $_response = array();
	/**
	 * @var \DreamFactory\Interfaces\FactoryService
	 */
	protected $_service = null;
	/**
	 * @var \Service
	 */
	protected $_serviceModel = null;

	//*************************************************************************
	//* Public Actions
	//*************************************************************************

	/**
	 * Initialize the controller
	 *
	 * @return void
	 */
	public function init()
	{
		//	Default to JSON
		$this->setOutputFormat( OutputFormat::JSON );

		parent::init();

		//	There's no layouts in REST!
		$this->layout = false;
		$this->defaultAction = 'index';

		//	Autoload some 'figs...
		$this->_loadOptions();
	}

	/**
	 * Provides a nice service page
	 */
	public function getIndex()
	{
		$_me = get_class( $this );
		$_mirror = new ReflectionClass( $_me );
		$_methods = null;

		foreach ( $_mirror->getMethods( ReflectionMethod::IS_PUBLIC ) as $_method )
		{
			foreach ( HttpMethod::getDefinedConstants() as $_name => $_value )
			{
				if ( $_method->class == $_me )
				{
					$_methodName = $_method->name;

					if ( strtolower( $_name ) == strtolower( substr( $_method->name, 0, strlen( $_name ) ) ) )
					{
						$_methodName = lcfirst( str_ireplace( $_name, null, $_methodName ) );
						$_methodPath = $this->getUniqueId() . '/' . $_methodName;

						//	Build a little form...
						$_id = Inflector::tag( $_methodPath, true );

						$_html = $this->renderPartial(
							'/_service_method',
							array(
								 'methodPath' => $_methodPath,
								 'httpMethod' => $_value,
								 'method'     => $_method,
								 'parameters' => $_method->getParameters(),
								 'id'         => Inflector::tag( $_method->name, true ),
								 'methodName' => $_methodName,
								 'target'     => '_blank',
								 'href'       => '/' . $this->getUniqueId() . '/' . $_methodName . '/',
								 'class'      => 'rest-method',
							),
							true
						);

						$_methods .= $_html;
					}
				}
			}
		}

		$this->layout = 'application.views.layouts.main';
		$this->setOutputFormat( OutputFormat::Raw );

		$this->render(
			'/service_methods',
			array(
				 'methods'            => $_methods,
				 'serviceDescription' => $this->_serviceModel->description_text,
			)
		);
	}

	/**
	 * Have a look-see at the inbound request and pull out any pertinent info.
	 * If this class has a setter for that property, it will be called with the
	 * value.
	 *
	 * @return bool True if any parameters were found and set.
	 */
	protected function _processHeaderParameters()
	{
		$_found = false;

		foreach ( $this->_headerParameters as $_key => $_property )
		{
			$_key = str_replace( '-', '_', strtoupper( 'http_' . $this->_headerParameterPrefix . $_key ) );

			//	If not found, check for parameter in $_REQUEST (without prefix!)
			if ( null === ( $_propertyValue = FilterInput::server( $_key ) ) && $this->_checkRequestForParameters )
			{
				$_propertyValue = FilterInput::request( $_property );
			}

			//	Set it, and forget it!
			if ( null !== $_propertyValue && method_exists( $this, 'set' . $_property ) )
			{
				$this->{'set' . $_property}( $_propertyValue );
				$_found = true;

				Log::debug( 'Found Header Parameter: ' . $_key . ' = ' . $_propertyValue );
			}
		}

		return $_found;
	}

	/**
	 * Provides the base response container for successful requests. Override to
	 * provide a different response container.
	 *
	 * @param array $results
	 *
	 * @return string JSON encoded string
	 */
	protected function _success( $results = array() )
	{
		$this->_response = array(
			'success' => 1,
		);

		foreach ( $results as $_key => $_value )
		{
			$this->_response[$_key] = $_value;
		}

		return json_encode( $this->_response );
	}

	/**
	 * Provides the base response container for successful requests. Override to
	 * provide a different response container.
	 *
	 * @param $error
	 *
	 * @return string JSON encoded string
	 */
	protected function _failure( $error )
	{
		$this->_response = array(
			'success' => 0,
			'error'   => $error,
		);

		return json_encode( $this->_response );
	}

	/**
	 * Validates inbound request. If this fails, an error response
	 * is triggered.
	 *
	 * @return bool
	 */
	protected function _validateRequest()
	{
		return true;
	}

	/**
	 * Loads default configuration options from the local configuration
	 * file and optionally from the $_POST array if this is a POST request.
	 *
	 * @throws Kisma\Core\Exceptions\InvalidSettingValueException
	 * @return void
	 */
	protected function _loadOptions()
	{
		//	If we have a service name, let's try and autoload some options
		if ( null === $this->_serviceName )
		{
			if ( $this instanceof BaseServiceController )
			{
				$this->_serviceName
					= lcfirst(
					Inflector::tag(
						str_ireplace(
							'controller',
							null,
							Inflector::tag( get_class( $this ), false, true )
						)
					)
				);

				Log::debug( 'Service name "' . $this->_serviceName . '" implied from handler.' );
			}
			else
			{
				throw new \Kisma\Core\Exceptions\InvalidSettingValueException( 'No "serviceName" found or specified.' );
			}
		}

		//	Let's see if we have our own config...
		if ( null !== ( $this->_serviceConfig = $this->_findServiceConfig() ) )
		{
			try
			{
				/** @noinspection PhpIncludeInspection */
				$this->_serviceConfig = @include( $this->_serviceConfig );
			}
			catch ( Exception $_ex )
			{
				//	Error reading file... meh
			}
		}

		//	Load the service row
		$this->_serviceModel = Service::model()->findByAttributes(
			array(
				 'service_tag_text' => $this->_serviceName,
			)
		);

		if ( null === $this->_serviceModel )
		{
			throw new \Kisma\Core\Exceptions\InvalidSettingValueException( 'No service record found for service "' . $this->_serviceName . '"' );
		}

		//	Take this opportunity to yank stuff out of the inbound request
		$this->_processHeaderParameters();

		//	Options may also be provided by a POST request, these OVERRIDE!
		if ( $this->_checkRequestForParameters && Pii::postRequest() )
		{
			$this->_loadOptionsFromArray( $_POST, true );
		}

		//	Set default output format to JSON
		$this->_outputFormat = OutputFormat::JSON;

		Log::debug( 'Service "' . $this->_serviceName . '" initialized.' );
	}

	/**
	 * Given an array of options, if any are in our list, we set them.
	 *
	 * @param array $options
	 * @param bool  $filter If true, vars will be filtered
	 *
	 * @return void
	 */
	protected function _loadOptionsFromArray( $options = array(), $filter = false )
	{
		// 	Loop through our options and see if they have values in the config file
		foreach ( $this->_configOptions as $_key => $_value )
		{
			if ( null !== ( $_value = \Kisma\Core\Utility\Option::get( $options, $_key ) ) )
			{
				$_setter = 'set' . $_key;

				//	If we have a setter and a value, do it.
				if ( method_exists( $this, $_setter ) )
				{
					//	Filter if desired (i.e. $_POST array)
					if ( $filter )
					{
						$_value = FilterInput::smart( $_value );
					}

					$this->{$_setter}( $_value );
				}
			}
		}
	}

	/**
	 * Adds an additional option to the $configOptions array.
	 *
	 * @param string $option
	 * @param mixed  $value
	 *
	 * @return \BaseServiceController
	 */
	protected function _addConfigOption( $option, $value = null )
	{
		$this->_configOptions[$option] = $value;

		return $this;
	}

	/**
	 * Adds multiple config options at once.
	 *
	 * @param array $options
	 *
	 * @return BaseServiceController
	 */
	protected function _addConfigOptions( array $options )
	{
		foreach ( $options as $_option => $_value )
		{
			$this->_addConfigOption( $_option, $_value );
		}

		return $this;
	}

	/**
	 * Adds an additional header parameter and its corresponding property
	 * option to the $headerParameters array.
	 *
	 * @param string $parameter
	 * @param string $propertyName
	 *
	 * @return \BaseServiceController
	 */
	protected function _addHeaderParameter( $parameter, $propertyName )
	{
		$this->_headerParameters[$parameter] = $propertyName;

		return $this;
	}

	/**
	 * Adds multiple header parameters at once.
	 *
	 * @param array $parameters
	 *
	 * @return BaseServiceController
	 */
	protected function _addHeaderParameters( array $parameters )
	{
		foreach ( $parameters as $_parameter => $_propertyName )
		{
			$this->_addHeaderParameter( $_parameter, $_propertyName );
		}

		return $this;
	}

	/**
	 * Overridden dispatcher to trap exceptions and return them as errors.
	 *
	 * @param CAction $action
	 *
	 * @return mixed
	 */
	protected function _dispatchRequest( CAction $action )
	{
		try
		{
			//	Dispatch the request
			$this->_response = parent::_dispatchRequest( $action );

			return $this->_response;
		}
		catch ( Exception $_ex )
		{
			Log::debug( 'Exception: ' . $_ex->getMessage() . ' (' . $_ex->getCode() . ')' );
			$this->_response = $this->_createErrorResponse( $_ex );
			echo $this->_formatOutput( $this->_response );
		}

		return $this->_response;
	}

	/**
	 * Looks for and returns the name of the configuration file for this service.
	 * Looks in the following places for the config file:
	 *
	 *         1.    module.basePath/config/serviceName.config.php
	 *         2.    app.config/serviceName.moduleId.config.php
	 *
	 * @return string
	 */
	protected function _findServiceConfig()
	{
		//	Are we a part of a module?
		if ( null === ( $_module = $this->getModule() ) )
		{
			$_module = Pii::app();
		}
		else
		{
			$_path = $_module->getBasePath() . '/config/' . $this->_serviceName . '.config.php';

			if ( file_exists( $_path ) )
			{
				return $_path;
			}
		}

		$_path = Yii::getPathOfAlias( 'application.config' ) . '/' . $this->_serviceName . '.' . $_module->getId() . '.config.php';

		if ( file_exists( $_path ) )
		{
			return $_path;
		}

		return null;
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param string $password
	 *
	 * @return void
	 */
	public function setPassword( $password = null )
	{
		$this->_password = $password;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->_password;
	}

	/**
	 * @param  $userName
	 *
	 * @return void
	 */
	public function setUserName( $userName = null )
	{
		$this->_userName = $userName;
	}

	/**
	 * @return string
	 */
	public function getUserName()
	{
		return $this->_userName;
	}

	/**
	 * @param string $accessToken
	 *
	 * @return \BaseServiceController
	 */
	public function setAccessToken( $accessToken )
	{
		$this->_accessToken = $accessToken;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->_accessToken;
	}

	/**
	 * @param string $headerParameterPrefix
	 *
	 * @return \BaseServiceController
	 */
	public function setHeaderParameterPrefix( $headerParameterPrefix )
	{
		$this->_headerParameterPrefix = $headerParameterPrefix;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHeaderParameterPrefix()
	{
		return $this->_headerParameterPrefix;
	}

	/**
	 * @param array $headerParameters
	 *
	 * @return \BaseServiceController
	 */
	public function setHeaderParameters( $headerParameters )
	{
		$this->_headerParameters = $headerParameters;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaderParameters()
	{
		return $this->_headerParameters;
	}

	/**
	 * @param boolean $requireAuthentication
	 *
	 * @return \BaseServiceController
	 */
	public function setRequireAuthentication( $requireAuthentication )
	{
		$this->_requireAuthentication = $requireAuthentication;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRequireAuthentication()
	{
		return $this->_requireAuthentication;
	}

	/**
	 * @param array $requiredParameters
	 *
	 * @return \BaseServiceController
	 */
	public function setRequiredParameters( $requiredParameters )
	{
		$this->_requiredParameters = $requiredParameters;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getRequiredParameters()
	{
		return $this->_requiredParameters;
	}

	/**
	 * @param array $response
	 *
	 * @return \BaseServiceController
	 */
	public function setResponse( $response )
	{
		$this->_response = $response;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * @param string $userLocation
	 *
	 * @return \BaseServiceController
	 */
	public function setUserLocation( $userLocation )
	{
		$this->_userLocation = $userLocation;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUserLocation()
	{
		return $this->_userLocation;
	}

	/**
	 * @param string $serviceName
	 *
	 * @return \BaseServiceController
	 */
	public function setServiceName( $serviceName )
	{
		$this->_serviceName = $serviceName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getServiceName()
	{
		return $this->_serviceName;
	}

	/**
	 * @param array $configOptions
	 *
	 * @return \BaseServiceController
	 */
	public function setConfigOptions( $configOptions )
	{
		$this->_configOptions = $configOptions;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getConfigOptions()
	{
		return $this->_configOptions;
	}

	/**
	 * @param boolean $checkRequestForParameters
	 *
	 * @return \BaseServiceController
	 */
	public function setCheckRequestForParameters( $checkRequestForParameters )
	{
		$this->_checkRequestForParameters = $checkRequestForParameters;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCheckRequestForParameters()
	{
		return $this->_checkRequestForParameters;
	}

	/**
	 * @param array $serviceConfig
	 *
	 * @return \BaseServiceController
	 */
	public function setServiceConfig( $serviceConfig )
	{
		$this->_serviceConfig = $serviceConfig;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getServiceConfig()
	{
		return $this->_serviceConfig;
	}

	/**
	 * @param \DreamFactory\Interfaces\FactoryService $service
	 *
	 * @return BaseServiceController
	 */
	public function setService( \DreamFactory\Interfaces\FactoryService $service )
	{
		$this->_service = $service;

		foreach ( \Kisma\Core\Utility\Option::clean( $this->_serviceConfig ) as $_key => $_value )
		{
			$this->_service->set( $_key, $_value );
		}

		return $this;
	}

	/**
	 * @return \DreamFactory\Interfaces\FactoryService
	 */
	public function getService()
	{
		return $this->_service;
	}

	/**
	 * @param \Services $serviceModel
	 *
	 * @return BaseServiceController
	 */
	public function setServiceModel( $serviceModel )
	{
		$this->_serviceModel = $serviceModel;

		return $this;
	}

	/**
	 * @return \Services
	 */
	public function getServiceModel()
	{
		return $this->_serviceModel;
	}
}
