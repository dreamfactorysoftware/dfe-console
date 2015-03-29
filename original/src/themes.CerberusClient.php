<?php
use Kisma\Core\Utility\Convert;
use Kisma\Core\Utility\Curl;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;

if ( null === Log::getDefaultLog() )
{
	Log::setDefaultLog( '/var/log/dreamfactory/dsp.module.log' );
}

/**
 * @file
 *            Provides sessions for Cerberus site to drupal
 * @copyright Copyright (c) 2012-2013 DreamFactory Software, Inc.
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 */
/**
 * CerberusClient
 */
class CerberusClient
{
	//*************************************************************************
	//* Class Constants
	//*************************************************************************

	/**
	 * @var string The default endpoint
	 */
	const DEFAULT_ENDPOINT = 'http://cerberus.fabric.dreamfactory.com/api/drupal';

	//*************************************************************************
	//* Private Members
	//*************************************************************************

	/**
	 * @var string
	 */
	protected static $_sessionId;
	/**
	 * @var string
	 */
	protected static $_token;
	/**
	 * @var null|stdClass
	 */
	protected static $_user;
	/**
	 * @var string
	 */
	protected static $_endpoint = self::DEFAULT_ENDPOINT;
	/**
	 * @var array
	 */
	protected static $_instances = array();
	/**
	 * @var \stdClass
	 */
	protected static $_trialInstance = null;

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Our dashboard gateway
	 */
	public static function getDashboardLink()
	{
		if ( user_is_anonymous() )
		{
			return false;
		}

		global $user;

		\drupal_goto( theme_get_setting( 'dreamfactory_dashboard_login_url' ) . '?token=' . sha1( $user->pass ) );
	}

	/**
	 * @return bool
	 */
	protected static function _processLogin()
	{
		//	Reset for a new user...
		static::$_sessionId = null;
		static::$_instances = array();
		static::$_token = null;
		static::$_user = Convert::toSimpleArray( static::$_user );
		static::$_trialInstance = static::trialInstance();

		return static::login();
	}

	/**
	 * @param string $url
	 * @param array  $payload
	 *
	 * @return bool|mixed|stdClass
	 */
	protected static function _apiCall( $url, $payload = array() )
	{
		global $user;

		if ( null === static::$_user && !empty( $user ) )
		{
			static::_processLogin( $user );
		}

		$_payload = array_merge(
			array(
				'user' => json_encode( static::$_user ),
			),
			$payload ? : array()
		);

		if ( !isset( $_payload['token'] ) && !empty( static::$_token ) )
		{
			$_payload['token'] = static::$_token;
		}

//		Log::debug( 'api "' . $url . '"  [REQUEST]: ' . print_r( $_payload, true ) );

		$_response = Curl::post(
						 static::$_endpoint . '/' . ltrim( $url, '/' ),
						 $_payload
		);

//		Log::debug( 'api "' . $url . '" [RESPONSE]: ' . print_r( $_payload, true ) );

		if ( $_response && isset( $_response->success, $_response->details ) )
		{
			return $_response->details;
		}

		if ( $_response && isset( $_response->resultData ) )
		{
			return $_response->resultData;
		}

		return $_response;
	}

	/**
	 * @return bool
	 */
	public static function inTrial()
	{
		return self::_apiCall( 'inTrial' );
	}

	/**
	 * @return bool|\stdClass
	 */
	public static function trialInstance()
	{
		return self::_apiCall( 'trialInstance' );
	}

	//*************************************************************************
	//* Drupal Handlers
	//*************************************************************************

	/**
	 * @param $form
	 * @param $form_state
	 */
	public static function formValidate( $form, $form_state )
	{
		if ( 0 == $form_state['values']['stop_trial'] )
		{
			$_host = trim( $form_state['values']['host_name'] );

			//	Check host name
			if ( !preg_match( HostPattern, $_host ) )
			{
				form_set_error(
					'host_name',
					t( 'The name must be between 6 and 63 characters long. It may contain only the letters A through Z, the numbers 0 through 9, and the dash (-) character.' )
				);
			}
		}
	}

	/**
	 * @param $form
	 * @param $form_state
	 */
	public static function formSubmit( $form, &$form_state )
	{
		if ( 1 == $form_state['values']['stop_trial'] )
		{
			static::endTrial();
			drupal_set_message( 'Your request to end your trial DSP is being processed. You will receive and email when it is complete.' );
			$form_state['redirect'] = '/';

			return;
		}

		static::launchTrial();
		drupal_set_message( 'Your request to start your trial DSP is being processed. You will receive and email when it is available.' );
		$form_state['redirect'] = 'dsp/launch';
	}

	/**
	 * @param $form
	 * @param $form_state
	 *
	 * @return mixed
	 */
	public static function form( $form, &$form_state )
	{
		if ( false !== ( $_instance = static::trialInstance() ) )
		{
			$form['host_name'] = array(
				'#type'       => 'textfield',
				'#title'      => 'Trial Instance Name/Url:',
				'#value'      => 'http://' . $_instance->instanceName . '.cloud.dreamfactory.com',
				'#size'       => 63,
				'#maxlength'  => 63,
				'#required'   => false,
				'#attributes' => array(
					'disabled' => 'disabled',
					'readonly' => 'readonly',
					'style'    => array( 'border-radius:4px;height:27px;width:auto;font-weight:bold;' ),
				),
			);

			$form['stop_trial'] = array(
				'#type'  => 'hidden',
				'#value' => 1,
			);

			$form['submit_button'] = array(
				'#type'  => 'submit',
				'#value' => t( 'End Trial' ),
			);
		}
		else
		{
			$form['host_name'] = array(
				'#type'       => 'textfield',
				'#title'      => 'Name',
				'#size'       => 63,
				'#maxlength'  => 63,
				'#required'   => true, //make this field required
				'#attributes' => array(
					'class' => array( 'input-medium' ),
				)
			);

			$form['stop_trial'] = array(
				'#type'  => 'hidden',
				'#value' => 0,
			);

			$form['submit_button'] = array(
				'#type'  => 'submit',
				'#value' => t( 'Launch!' ),
			);
		}

		return $form;
	}

	/**
	 * Builds our menu items
	 *
	 * @param bool $inTrial
	 *
	 * @return array
	 */
	public static function menu( $inTrial = false )
	{
		static::login();

		$_menuItems = array();

		$_title = ( false === $inTrial ) ? 'Start Your Free Trial' : 'Your Current Free Trial';

		//$_menuItems['dsp'] = array(
		$_menuItems['dsp/launch'] = array(
			'title'            => $_title,
			'page callback'    => 'drupal_get_form',
			'page arguments'   => array( 'dsp_form' ),
			'access callback'  => true,
			'access arguments' => array( 'access content information' ),
			'menu_name'        => 'Developers',
		);

		$_menuItems['user/dashboard'] = array
		(
			'title'           => 'Dashboard',
			'page callback'   => 'drupal_goto',
			'page arguments'  => array( '/user/dashboard' ),
			'access callback' => 'CerberusClient::userValidated',
		);

		//);

		return $_menuItems;
	}

	public static function userValidated()
	{
		return !\user_is_anonymous();
	}

	/**
	 * @return bool
	 */
	public static function login()
	{
		if ( false === ( $_response = self::_apiCall( 'user' ) ) )
		{
			drupal_set_message( 'Unable to contact the authentication system.', 'error' );

			return false;
		}

		if ( isset( $_response->id, $_response->token ) )
		{
			static::$_token = $_response->token;
			static::$_sessionId = $_response->id;
			static::$_instances = $_response->instances;
		}

		return $_response;
	}

	/**
	 *
	 */
	public static function logout()
	{
		static::$_token = static::$_sessionId = static::$_user = null;
	}

	/**
	 * @return bool|mixed|stdClass
	 */
	public static function launchTrial()
	{
		$_payload = array(
			'user'  => json_encode( static::$_user ),
			'token' => static::$_token,
		);

		if ( false === ( $_response = self::_apiCall( 'launch', $_payload ) ) )
		{
			drupal_set_message( 'Error requesting new trial start: ' . static::$_endpoint, 'error' );

			return false;
		}

		Log::debug( 'Launch response: ' . print_r( $_response, true ) );

		if ( $_response->result !== 'success' )
		{
			drupal_set_message( $_response->errorMessage, 'error' );

			return false;
		}

		return $_response;
	}

	/**
	 * @return bool|mixed|stdClass
	 */
	public static function endTrial()
	{
		if ( null === static::$_trialInstance )
		{
			static::$_trialInstance = static::trialInstance();
		}

		if ( false === ( $_response = self::_apiCall( 'destroy', array( 'name' => static::$_trialInstance->instanceName ) ) ) )
		{
			drupal_set_message( 'Error requesting trial destruction: ' . static::$_endpoint, 'error' );

			return false;
		}

		Log::debug( 'Destroy response: ' . print_r( $_response, true ) );

		if ( false === $_response )
		{
			drupal_set_message( 'Unable to deprovision your trial at this time. Please try again later.', 'error' );

			return false;
		}

		return $_response;
	}

	/**
	 * Implementation of hook_user_login().
	 *
	 * @param array    $edit
	 * @param stdClass $account
	 *
	 * @return bool
	 */
	public static function userLogin( &$edit, $account )
	{
		//	Not logged in
		if ( 0 == static::$_user->uid )
		{
			$edit['redirect'] = '/';

			return;
		}

		CerberusClient::updateHubspot( $account );

		return static::_processLogin();
	}

	/**
	 * Logout
	 */
	public static function userLogout()
	{
		unset( static::$_user->dsp_session );
	}

	/**
	 * @param string $name
	 * @param string $id
	 *
	 * @return bool
	 */
	public static function getDsp( $name = null, $id = null )
	{
		$_payload = array();

		if ( null !== $name )
		{
			$_payload['name'] = $name;
		}

		if ( null !== $id )
		{
			$_payload['id'] = $id;
		}

		if ( false === ( $_instance = self::_apiCall( 'instance', $_payload ) ) )
		{
			drupal_set_message( 'Unable to contact the provisioning system.' );

			return false;
		}

		return $_instance;
	}

	/**
	 * Compile a list holding all supported editors including installed editor version information.
	 */
	public static function getAllDsps()
	{
		if ( false === ( $_instances = self::_apiCall( 'instances' ) ) )
		{
			drupal_set_message( 'Unable to contact the provisioning system.' );

			return false;
		}

		return $_instances;
	}

	/**
	 * @param string $name
	 * @param string $id
	 *
	 * @return bool
	 */
	public static function getDspStatus( $name = null, $id = null )
	{
		$_payload = array();

		if ( null !== $name )
		{
			$_payload['name'] = $name;
		}

		if ( null !== $id )
		{
			$_payload['id'] = $id;
		}

		if ( false === ( $_status = self::_apiCall( 'status', $_payload ) ) )
		{
			drupal_set_message( 'Unable to contact the provisioning system.' );

			return false;
		}

		return $_status;
	}

	/**
	 * @param \stdClass $user
	 * @param string    $portalId
	 * @param string    $formGuid
	 * @param bool      $newUser
	 */
	public static function updateHubspot( $user, $portalId = null, $formGuid = null, $newUser = false )
	{
		if ( $user->mail == 'o.p.s@dreamfactory.com' )
		{
			Log::info( 'Ignoring rest user' );

			return true;
		}

		$_portal = $portalId ? : theme_get_setting( 'dreamfactory_default_hubspot_portal_id' );
		$_guid = $formGuid
			? : ( $newUser
				? theme_get_setting( 'dreamfactory_default_hubspot_form_guid' )
				: theme_get_setting(
					'dreamfactory_default_hubspot_update_form_guid'
				)
			);

		//	Prepare the post...
		$_payload = array(
			'User Name'                  => $user->name,
			'Date Created'               => date( 'c', $user->created ),
			'Last Login Date'            => $user->login ? date( 'c', $user->login ) : 'Never',
			'Time Zone'       => ((isset($user->timezone))?$user->timezone:0),
			'firstname'                  => static::fieldValue( $user, 'first_name' ),
			'lastname'                   => static::fieldValue( $user, 'last_name' ),
			'email'                      => $user->mail,
			'phone'                      => static::fieldValue( $user, 'phone_number' ),
			'company'                    => static::fieldValue( $user, 'company_name' ),
			'jobtitle'                   => static::fieldValue( $user, 'title' ),
			'address'                    => static::fieldValue( $user, 'address_1' ),
			'address2'                   => static::fieldValue( $user, 'address_2' ),
			'city'                       => static::fieldValue( $user, 'city' ),
			'state'                      => static::fieldValue( $user, 'state_province' ),
			'zip'                        => static::fieldValue( $user, 'zip_postal_code' ),
			'hs_context'                 => json_encode(
				array(
					'hutk'      => FilterInput::cookie( 'hubspotutk' ),
					'ipAddress' => $_SERVER['REMOTE_ADDR'],
					'pageUrl'   => $_SERVER['HTTP_REFERER'],
					'pageName'  => $_SERVER['REQUEST_URI'],
				)
			),
			//	2014-01-06 GHA New fields to indicate support opt-in
			'local_installation'         => static::fieldValue( $user, 'welcome_registration' ),
			'local_installation_skipped' => static::fieldValue( $user, 'welcome_skipped' ),
			'hs_email_optout'            => static::fieldValue( $user, 'welcome_skipped' ),
		);

		$_result = Curl::post(
					   'https://forms.hubspot.com/uploads/form/v2/' . $_portal . '/' . $_guid,
					   $_payload,
					   array(
						   CURLOPT_HTTPHEADER => array(
							   'application/x-www-form-urlencoded'
						   )
					   )
		);

		Log::debug( 'HubSpot POST: ' . 'https://forms.hubspot.com/uploads/form/v2/' . $_portal . '/' . $_guid );
		Log::debug( '     Payload: ' . print_r( $_payload, true ) . PHP_EOL . 'Server: ' . PHP_EOL . print_r( $_SERVER, true ) );

		Log::info( 'HubSpot update response: ' . print_r( $_result, true ) );
		Log::debug( '  * Curl Info          : ' . print_r( Curl::getInfo(), true ) );
	}

	/**
	 * @param string $endpoint
	 *
	 * @return DevWebClient
	 */
	public static function setEndpoint( $endpoint )
	{
		static::$_endpoint = $endpoint;
	}

	/**
	 * @return string
	 */
	public static function getEndpoint()
	{
		return static::$_endpoint;
	}

	/**
	 * @param string $sessionId
	 *
	 * @return DevWebClient
	 */
	public static function setSessionId( $sessionId )
	{
		static::$_sessionId = $sessionId;
	}

	/**
	 * @return string
	 */
	public static function getSessionId()
	{
		return static::$_sessionId;
	}

	/**
	 * @param string $token
	 *
	 * @return DevWebClient
	 */
	public static function setToken( $token )
	{
		static::$_token = $token;
	}

	/**
	 * @return string
	 */
	public static function getToken()
	{
		return static::$_token;
	}

	/**
	 * @param \stdClass|array $user
	 *
	 * @return array|\stdClass
	 */
	public static function setUser( $user )
	{
		return static::$_user = $user;
	}

	/**
	 * @return null|\stdClass
	 */
	public static function getUser()
	{
		return static::$_user;
	}

	/**
	 * @param \stdClass|array $object
	 * @param string          $field
	 * @param mixed           $defaultValue
	 * @param string          $languageCode
	 *
	 * @return array
	 */
	public static function fieldValue( $object, $field, $defaultValue = null, $languageCode = LANGUAGE_NONE )
	{
		$_values = array();

		if ( null === ( $_item = Option::getDeep( $object, $field, $languageCode, $defaultValue ) ) )
		{
			if ( 'field_' != substr( $field, 0, 6 ) )
			{
				$field = 'field_' . $field;
			}

			if ( null === ( $_item = Option::getDeep( $object, $field, $languageCode, $defaultValue ) ) )
			{
				return null;
			}
		}

		if ( is_array( $_item ) || is_object( $_item ) || $_item instanceof \Traversable )
		{
			foreach ( $_item as $_value )
			{
				$_values[] = $_value['value'];
			}
		}

		if ( empty( $_values ) )
		{
			return null;
		}

		if ( 1 == sizeof( $_values ) )
		{
			return current( $_values );
		}

		return $_values;
	}
}
