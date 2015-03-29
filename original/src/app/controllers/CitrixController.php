<?php
use Kisma\Core\Utility\Log;

/**
 * CitrixController.php
 */
/**
 * CitrixController class
 * This is the endpoint for the G2M/G2W REST/OAuth API
 */
class CitrixController extends BaseServiceController
{
	//*************************************************************************
	//* Private Members
	//*************************************************************************

	/**
	 * @var array The keys for my services
	 */
	protected $_oauthKeys = array();
	/**
	 * @var string
	 */
	protected $_authorizeUrl = null;
	/**
	 * @var string Used to exchange/upgrade request token/response key to access token
	 */
	protected $_accessTokenUrl = null;
	/**
	 * @var string
	 */
	protected $_redirectUri = null;
	/**
	 * @var string
	 */
	protected $_organizerKey = null;

	//*************************************************************************
	//* Public Actions
	//*************************************************************************

	/**
	 * Initializes the controller. By the time we get to this method, the header
	 * and request have been scraped for parameters defined in $headerParameters
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();

		//	Set default output format to JSON
		$this->_outputFormat = \PS::OF_JSON;

		//	Set our access rules..
		$this->addUserActions(
			self::Any,
			array(
				 'g2m',
				 'g2w',
				 'g2t',
				 'authorize',
			)
		);

		if ( null === $this->_accessToken )
		{
			if ( null !== ( $this->_accessToken = \PS::_gs( 'accessToken' ) ) )
			{
				Log::debug( 'Got accessToken from session: ' . $this->_accessToken );
			}
		}

		if ( null === $this->_organizerKey )
		{
			if ( null !== ( $this->_organizerKey = \PS::_gs( 'organizerKey' ) ) )
			{
				Log::debug( 'Got organizerKey from session: ' . $this->_organizerKey );
			}
		}
	}

	/**
	 * @return string
	 */
	public function getAuthorize()
	{
		$this->_outputFormat = \PS::OF_RAW;

		$_url = base64_decode( urldecode( \PS::o( $_REQUEST, 'url' ) ) );
		$_callback = base64_decode( urldecode( \PS::o( $_REQUEST, 'callback' ) ) );
		$_mode = \PS::o( $_REQUEST, 'mode' );
		\PS::_ss( 'mode', $_mode );
		\PS::_ss( 'postAuthCallback', $_callback );

		Log::debug( 'Authorize request url: ' . $_url );
		Log::debug( 'Authorize callback: ' . $_callback );
		Log::debug( 'Authorize mode: ' . $_mode );

		Log::debug( 'callback check: ' . \PS::_gs( 'postAuthCallback' ) );

		header( 'Location: ' . $_url );
	}

	/**
	 * GoToMeeting Request Handler
	 */
	public function requestG2M()
	{
		$this->_outputFormat = \PS::OF_RAW;

		if ( false === ( $_token = $this->_checkOAuthProgress( 'g2m' ) ) )
		{
			$this->render(
				'index',
				array(
					 'authorizeUrl' => $this->_getServiceUrl( 'g2m', $this->_authorizeUrl ),
				)
			);

			return;
		}

		$this->render(
			'authorized',
			array(
				 'token' => $_token,
			)
		);
	}

	/**
	 * GoToWebinar Request Handler
	 *
	 * @return \MeetingInfo|string
	 */
	public function requestG2W()
	{
		$this->_outputFormat = \PS::OF_RAW;

		if ( false === ( $_token = $this->_checkOAuthProgress( 'g2w' ) ) )
		{
			$this->render(
				'index',
				array(
					 'authorizeUrl' => $this->_getServiceUrl( 'g2w', $this->_authorizeUrl ),
				)
			);

			return;
		}

		$this->render(
			'authorized',
			array(
				 'token' => $_token,
			)
		);
	}

	//*************************************************************************
	//* Private Methods
	//*************************************************************************

	/**
	 * Does param subst on the url
	 *
	 * @param string      $serviceName
	 * @param string      $baseUrl
	 * @param string|null $responseKey
	 *
	 * @return string
	 */
	protected function _getServiceUrl( $serviceName, $baseUrl, $responseKey = null )
	{
		return str_ireplace(
			array(
				 '%%responseKey%%',
				 '%%consumerKey%%',
			),
			array(
				 $responseKey,
				 $this->_oauthKeys[$serviceName]['consumerKey'],
			),
			$baseUrl
		);
	}

	/**
	 * Checks the progress of any in-flight OAuth transactions
	 *
	 * @param string $serviceName
	 *
	 * @return bool|\stdClass
	 */
	protected function _checkOAuthProgress( $serviceName )
	{
		//	OAuth Step #2, get responseKey/requestToken
		if ( !isset( $_REQUEST, $_REQUEST['code'] ) )
		{
			//	Nothing here, move along
			return false;
		}

		//	This is the response key from the authorization request
		$_responseKey = trim( filter_var( $_REQUEST['code'], FILTER_SANITIZE_STRING ) );

		//	Upgrade token to an access token...
		if ( empty( $_responseKey ) )
		{
			//	Bogus
			return false;
		}

		Log::debug( 'Received response key: ' . $_responseKey );

		//	Construct the url
		$_url = $this->_getServiceUrl( $serviceName, $this->_accessTokenUrl, $_responseKey );
		Log::debug( 'Exchange response code url: ' . $_url );

		//	Exchange response key for an access token...
		$_tokenResponse = \PS::makeHttpRequest( $_url );
		Log::debug( 'Response received from accessTokenUrl: ' . print_r( $_tokenResponse, true ) );

		$_token = json_decode( $_tokenResponse );

		if ( isset( $_token->access_token ) )
		{
			\PS::_ss( 'accessToken', $this->_accessToken = $_token->access_token );
			Log::debug( 'Access token received: ' . $this->_accessToken );

			if ( isset( $_token->organizer_key ) )
			{
				\PS::_ss( 'organizerKey', $this->_organizerKey = $_token->organizer_key );
				Log::debug( 'Organizer Key received: ' . $this->_organizerKey );
			}

			$_callback = \PS::_gs( 'postAuthCallback' );

			Log::debug( 'P`ostAuthCallback URL: ' . $_callback );

			if ( null !== $_callback )
			{
				\PS::_ss( 'postAuthCallback', null );
				header(
					'Location: ' . $_callback . '?mode=' . $serviceName . '&access_token=' . urlencode( $this->_accessToken ) . '&organizer_key=' .
					urlencode( $this->_organizerKey ) . '&refresh_token=' . urlencode( $_token->refresh_token )
				);
			}
		}

		//	Return the token as an object
		return $_token;
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param string $accessToken
	 *
	 * @return \CitrixController
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
	 * @param string $accessTokenUrl
	 *
	 * @return \CitrixController
	 */
	public function setAccessTokenUrl( $accessTokenUrl )
	{
		$this->_accessTokenUrl = $accessTokenUrl;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccessTokenUrl()
	{
		return $this->_accessTokenUrl;
	}

	/**
	 * @param string $authorizeUrl
	 *
	 * @return \CitrixController
	 */
	public function setAuthorizeUrl( $authorizeUrl )
	{
		$this->_authorizeUrl = $authorizeUrl;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAuthorizeUrl()
	{
		return $this->_authorizeUrl;
	}

	/**
	 * @param array $oauthKeys
	 *
	 * @return \CitrixController
	 */
	public function setOauthKeys( $oauthKeys )
	{
		$this->_oauthKeys = $oauthKeys;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getOauthKeys()
	{
		return $this->_oauthKeys;
	}

	/**
	 * @param string $redirectUri
	 *
	 * @return \CitrixController
	 */
	public function setRedirectUri( $redirectUri )
	{
		$this->_redirectUri = $redirectUri;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRedirectUri()
	{
		return $this->_redirectUri;
	}

	/**
	 * @param string $organizerKey
	 *
	 * @return \CitrixController
	 */
	public function setOrganizerKey( $organizerKey )
	{
		$this->_organizerKey = $organizerKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOrganizerKey()
	{
		return $this->_organizerKey;
	}
}