<?php
/**
 * @file
 *         OAuth 2.0 server in PHP, originally written for
 * <a href="http://www.opendining.net/"> Open Dining</a>. Supports
 * <a href="http://tools.ietf.org/html/draft-ietf-oauth-v2-20">IETF draft v20</a>.
 *         Ported from https://github.com/dschniepp/Laravel-OAuth2-Server
 *
 * @author Tim Ridgely <tim.ridgely@gmail.com>
 * @author Aaron Parecki <aaron@parecki.com>
 * @author Edison Wong <hswong3i@pantarei-design.com>
 * @author David Rochwerger <catch.dave@gmail.com>
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 *
 * @see    http://code.google.com/p/oauth2-php/
 * @see    https://github.com/quizlet/oauth2-php
 * @see    https://github.com/dschniepp/Laravel-OAuth2-Server
 */
namespace Cerberus\Services\OAuth;

use DreamFactory\Exceptions\OAuth\AuthenticationException;
use DreamFactory\Exceptions\OAuth\RedirectException;
use DreamFactory\Exceptions\OAuth\ServerException;
use DreamFactory\Interfaces\OAuth\GrantClient;
use DreamFactory\Interfaces\OAuth\GrantCode;
use DreamFactory\Interfaces\OAuth\GrantExtension;
use DreamFactory\Interfaces\OAuth\GrantUser;
use DreamFactory\Interfaces\OAuth\OAuth2Server;
use DreamFactory\Interfaces\OAuth\OAuth2Storage;
use DreamFactory\Interfaces\OAuth\RefreshToken;
use Kisma\Core\Interfaces\HttpResponse;
use Kisma\Core\SeedBag;
use Kisma\Core\Utility\FilterInput;

/**
 * Server
 * OAuth2.0 draft v20 server-side implementation.
 *
 * @todo   Add support for Message Authentication Code (MAC) token type.
 *
 * @author Originally written by Tim Ridgely <tim.ridgely@gmail.com>.
 * @author Updated to draft v10 by Aaron Parecki <aaron@parecki.com>.
 * @author Debug, coding style clean up and documented by Edison Wong <hswong3i@pantarei-design.com>.
 * @author Refactored (including separating from raw POST/GET) and updated to draft v20 by David Rochwerger <catch.dave@gmail.com>.
 * @author Refactored again by Jerry Ablan <jerryablan@dreamfactory.com>
 */
class Server extends SeedBag implements OAuth2Server, HttpResponse
{
	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * Server configuration
	 *
	 * @var array
	 */
	protected $_config = array();
	/**
	 * Storage engine for authentication server
	 *
	 * @var \DreamFactory\Interfaces\OAuth\OAuth2Storage
	 */
	protected $_tokenStore;
	/**
	 * Keep track of the old refresh token. So we can unset
	 * the old refresh tokens when a new one is issued.
	 *
	 * @var string
	 */
	protected $_oldRefreshToken;

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Creates an OAuth2.0 server instance.
	 *
	 * @param \DreamFactory\Interfaces\OAuth\OAuth2Storage $storage
	 * @param array                                        $config
	 */
	public function __construct( OAuth2Storage $storage, $config = array() )
	{
		$this->_tokenStore = $storage;
		$this->_setDefaultOptions();

		parent::__construct( $config );
	}

	//.........................................................................
	//. Resource protecting (Section 5).
	//.........................................................................

	/**
	 * Check that a valid access token has been provided.
	 * The token is returned (as an associative array) if valid.
	 *
	 * The scope parameter defines any required scope that the token must have.
	 * If a scope param is provided and the token does not have the required
	 * scope, we bounce the request.
	 *
	 * Some implementations may choose to return a subset of the protected
	 * resource (i.e. "public" data) if the user has not provided an access
	 * token or if the access token is invalid or expired.
	 *
	 * The IETF spec says that we should send a 401 Unauthorized header and
	 * bail immediately so that's what the defaults are set to. You can catch
	 * the exception thrown and behave differently if you like (log errors, allow
	 * public access for missing tokens, etc)
	 *
	 * @param string $tokenToCheck
	 * @param string $scope A space-separated string of required scope(s), if you want to check for scope.
	 *
	 * @throws \DreamFactory\Exceptions\OAuth\AuthenticationException
	 * @return array
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-7
	 */
	public function verifyAccessToken( $tokenToCheck, $scope = null )
	{
		$_tokenType = $this->get( self::ConfigTokenType );
		$_realm = $this->get( self::ConfigRealm );

		if ( empty( $tokenToCheck ) )
		{
			//	Access token was not provided
			throw new AuthenticationException( static::BadRequest, $_tokenType, $_realm, static::Error_InvalidRequest, static::ErrorMessage_InvalidRequest, $scope );
		}

		//	Get the stored token data (from the implementing subclass)
		if ( null === ( $_storedToken = $this->_tokenStore->getAccessToken( $tokenToCheck ) ) )
		{
			throw new AuthenticationException( self::Unauthorized, $_tokenType, $_realm, static::Error_InvalidGrant, static::ErrorMessage_InvalidAccessToken, $scope );
		}

		//	Check we have a well formed token
		if ( !isset( $_storedToken['expires'] ) || !isset( $_storedToken['client_id'] ) )
		{
			throw new AuthenticationException( self::Unauthorized, $_tokenType, $_realm, static::Error_InvalidGrant, static::ErrorMessage_MalformedToken, $scope );
		}

		//	Check token expiration (expires is a mandatory parameter)
		if ( isset( $_storedToken['expires'] ) && time() > $_storedToken['expires'] )
		{
			throw new AuthenticationException( self::Unauthorized, $_tokenType, $_realm, static::Error_InvalidGrant, static::ErrorMessage_ExpiredToken, $scope );
		}

		//	Check scope, if provided
		if ( null !== $scope && ( !isset( $_storedToken['scope'] ) || !$_storedToken['scope'] || !$this->_checkScope( $scope, $_storedToken['scope'] ) ) )
		{
			throw new AuthenticationException( self::Forbidden, $_tokenType, $_realm, self::Error_InsufficientScope, static::ErrorMessage_InsufficientScope, $scope );
		}

		return $_storedToken;
	}

	/**
	 * This is a convenience function that can be used to get the token, which can then
	 * be passed to verifyAccessToken().
	 *
	 * As per the Bearer spec (draft 8, section 2) - there are three ways for a client
	 * to specify the bearer token, in order of preference: Authorization Header,
	 * POST and GET.
	 *
	 * NB: Resource servers MUST accept tokens via the Authorization scheme
	 * (http://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-08#section-2).
	 *
	 * @todo Should we enforce TLS/SSL in this function?
	 *
	 * @see  http://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-08#section-2.1
	 * @see  http://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-08#section-2.2
	 * @see  http://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-08#section-2.3
	 *
	 * Old Android version bug (at least with version 2.2)
	 * @see  http://code.google.com/p/android/issues/detail?id=6684
	 *
	 * We don't want to test this functionality as it relies on superglobals and headers:
	 * @codeCoverageIgnoreStart
	 */
	public function getBearerToken()
	{
		if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) )
		{
			$_headers = trim( $_SERVER["HTTP_AUTHORIZATION"] );
		}
		elseif ( function_exists( 'apache_request_headers' ) )
		{
			$_requestHeaders = apache_request_headers();

			//	Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$_requestHeaders = array_combine( array_map( 'ucwords', array_keys( $_requestHeaders ) ), array_values( $_requestHeaders ) );

			if ( isset( $_requestHeaders['Authorization'] ) )
			{
				$_headers = trim( $_requestHeaders['Authorization'] );
			}
		}

		$_tokenType = $this->get( static::ConfigTokenType );
		$_realm = $this->get( static::ConfigRealm );

		//	Check that exactly one method was used
		$_methodsUsed = !empty( $_headers ) + isset( $_GET[static::TokenParameterName] ) + isset( $_POST[self::TokenParameterName] );

		if ( $_methodsUsed > 1 )
		{
			throw new AuthenticationException( static::BadRequest, $_tokenType, $_realm, self::Error_InvalidRequest, 'Only one method may be used to authenticate at a time (Auth header, GET or POST).' );
		}
		elseif ( $_methodsUsed == 0 )
		{
			throw new AuthenticationException( static::BadRequest, $_tokenType, $_realm, self::Error_InvalidRequest, 'The access token was not found.' );
		}

		//	HEADER: Get the access token from the header
		if ( !empty( $_headers ) )
		{
			if ( !preg_match( '/' . self::TokenBearerHeaderName . '\s(\S+)/', $_headers, $_matches ) )
			{
				throw new AuthenticationException( static::BadRequest, $_tokenType, $_realm, self::Error_InvalidRequest, 'Malformed auth header' );
			}

			return $_matches[1];
		}

		// POST: Get the token from POST data
		if ( isset( $_POST[self::TokenParameterName] ) )
		{
			if ( 'POST' != $_SERVER['REQUEST_METHOD'] )
			{
				throw new AuthenticationException( static::BadRequest, $_tokenType, $_realm, self::Error_InvalidRequest, 'When putting the token in the body, the method must be POST.' );
			}

			//	IETF specifies content-type. NB: Not all servers populate this _SERVER variable
			if ( null !== ( $_contentType = FilterInput::server( 'CONTENT_TYPE' ) ) && 'application/x-www-form-urlencoded' != $_contentType )
			{
				throw new AuthenticationException( static::BadRequest, $_tokenType, $_realm, self::Error_InvalidRequest, 'The content type for POST requests must be "application/x-www-form-urlencoded"' );
			}

			return $_POST[self::TokenParameterName];
		}

		//	GET method
		return $_GET[self::TokenParameterName];
	}

	/**
	 * Check if everything in required scope is contained in available scope.
	 *
	 * @param array|string $requiredScope Required scope to be check with.
	 * @param array|string $availableScope
	 *
	 * @return bool TRUE if everything in required scope is contained in available scope,
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-7
	 */
	protected function _checkScope( $requiredScope, $availableScope )
	{
		//	The required scope should match or be a subset of the available scope
		if ( !is_array( $requiredScope ) )
		{
			$requiredScope = explode( ' ', trim( $requiredScope ) );
		}

		if ( !is_array( $availableScope ) )
		{
			$availableScope = explode( ' ', trim( $availableScope ) );
		}

		return ( count( array_diff( $requiredScope, $availableScope ) ) == 0 );
	}

	//.........................................................................
	//. Access token granting (Section 4).
	//.........................................................................

	/**
	 * Grant or deny a requested access token.
	 * This would be called from the "/token" endpoint as defined in the spec.
	 * Obviously, you can call your endpoint whatever you want.
	 *
	 * @param array $inputData - The draft specifies that the parameters should be
	 *                         retrieved from POST, but you can override to whatever method you like.
	 *
	 * @param array $authHeaders
	 *
	 * @throws \DreamFactory\Exceptions\OAuth\ServerException
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-10.6
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-4.1.3
	 */
	public function grantAccessToken( array $inputData = null, array $authHeaders = null )
	{
		$_filters = array(
			'grant_type'    => array(
				'filter'  => FILTER_VALIDATE_REGEXP,
				'options' => array( 'regexp' => self::RegExpFilter_GrantType ),
				'flags'   => FILTER_REQUIRE_SCALAR
			),
			'scope'         => array( 'flags' => FILTER_REQUIRE_SCALAR ),
			'code'          => array( 'flags' => FILTER_REQUIRE_SCALAR ),
			'redirect_uri'  => array( 'filter' => FILTER_SANITIZE_URL ),
			'username'      => array( 'flags' => FILTER_REQUIRE_SCALAR ),
			'password'      => array( 'flags' => FILTER_REQUIRE_SCALAR ),
			'refresh_token' => array( 'flags' => FILTER_REQUIRE_SCALAR ),
		);

		// Input data by default can be either POST or GET
		if ( empty( $inputData ) )
		{
			$inputData = ( 'POST' == $_SERVER['REQUEST_METHOD'] ) ? $_POST : $_GET;
		}

		// Basic authorization header
		$authHeaders = $authHeaders ? : $this->_getAuthorizationHeader();

		// Filter input data
		$_input = filter_var_array( $inputData, $_filters );

		// Grant Type must be specified.
		if ( !$_input['grant_type'] )
		{
			throw new ServerException( static::BadRequest, self::Error_InvalidRequest, 'Invalid grant_type parameter or parameter missing' );
		}

		//	Authorize the client
		$_client = $this->_getClientCredentials( $inputData, $authHeaders );

		if ( false === $this->_tokenStore->checkClientCredentials( $_client[0], $_client[1] ) )
		{
			throw new ServerException( static::BadRequest, self::Error_InvalidClient, 'The client credentials are invalid' );
		}

		if ( !$this->_tokenStore->checkRestrictedGrantType( $_client[0], $_input['grant_type'] ) )
		{
			throw new ServerException( static::BadRequest, self::Error_UnsupportedGrantType, 'The grant type is unauthorized for this client_id' );
		}

		//	Do the granting
		switch ( $_input['grant_type'] )
		{
			case self::GrantTypeAuthCode:
				if ( !( $this->_tokenStore instanceof GrantCode ) )
				{
					throw new ServerException( static::BadRequest, self::Error_UnsupportedGrantType );
				}

				if ( !$_input['code'] )
				{
					throw new ServerException( static::BadRequest, self::Error_InvalidRequest, 'Missing parameter. "code" is required' );
				}

				if ( $this->get( self::ConfigEnforceInputRedirect ) && !$_input['redirect_uri'] )
				{
					throw new ServerException( static::BadRequest, self::Error_InvalidRequest, 'The redirect URI parameter is required.' );
				}

				$_clientDetails = $this->_tokenStore->getAuthCode( $_input['code'] );

				//	Check the code exists
				if ( null === $_clientDetails || $_client[0] != $_clientDetails['client_id'] )
				{
					throw new ServerException( static::BadRequest, static::Error_InvalidGrant, 'Refresh token does not exist or is invalid for the client' );
				}

				// Validate the redirect URI. If a redirect URI has been provided on input, it must be validated
				if ( $_input['redirect_uri'] && !$this->validateRedirectUri( $_input['redirect_uri'], $_clientDetails['redirect_uri'] ) )
				{
					throw new ServerException( static::BadRequest, self::Error_RedirectUriMismatch, 'The redirect URI is missing or do not match' );
				}

				if ( $_clientDetails['expires'] < time() )
				{
					throw new ServerException( static::BadRequest, static::Error_InvalidGrant, 'The authorization code has expired' );
				}
				break;

			case self::GrantTypeUserCredentials:
				if ( !( $this->_tokenStore instanceof GrantUser ) )
				{
					throw new ServerException( static::BadRequest, self::Error_UnsupportedGrantType );
				}

				if ( !$_input['username'] || !$_input['password'] )
				{
					throw new ServerException( static::BadRequest, self::Error_InvalidRequest, 'Missing parameters. "username" and "password" required' );
				}

				$_clientDetails = $this->_tokenStore->checkUserCredentials( $_client[0], $_input['username'], $_input['password'] );

				if ( $_clientDetails === false )
				{
					throw new ServerException( static::BadRequest, static::Error_InvalidGrant );
				}
				break;

			case self::GrantTypeClientCredentials:
				if ( !( $this->_tokenStore instanceof GrantClient ) )
				{
					throw new ServerException( static::BadRequest, self::Error_UnsupportedGrantType );
				}

				if ( empty( $_client[1] ) )
				{
					throw new ServerException( static::BadRequest, self::Error_InvalidClient, 'The client_secret is mandatory for the "client_credentials" grant type' );
				}
				// NB: We don't need to check for $_clientDetails==false, because it was checked above already
				$_clientDetails = $this->_tokenStore->checkClientCredentialsGrant( $_client[0], $_client[1] );
				break;

			case self::GrantTypeRefreshToken:
				if ( !( $this->_tokenStore instanceof RefreshToken ) )
				{
					throw new ServerException( static::BadRequest, self::Error_UnsupportedGrantType );
				}

				if ( !$_input['refresh_token'] )
				{
					throw new ServerException( static::BadRequest, self::Error_InvalidRequest, 'No "refresh_token" parameter found' );
				}

				$_clientDetails = $this->_tokenStore->getRefreshToken( $_input['refresh_token'] );

				if ( $_clientDetails === null || $_client[0] != $_clientDetails['client_id'] )
				{
					throw new ServerException( static::BadRequest, static::Error_InvalidGrant, 'Invalid refresh token' );
				}

				if ( $_clientDetails['expires'] < time() )
				{
					throw new ServerException( static::BadRequest, static::Error_InvalidGrant, 'Refresh token has expired' );
				}

				// store the refresh token locally so we can delete it when a new refresh token is generated
				$this->_oldRefreshToken = $_clientDetails['refresh_token'];
				break;

			case self::GrantTypeImplicit:
				/* TODO: NOT YET IMPLEMENTED */
				throw new ServerException( '501 Not Implemented', 'This server does not support this grant type.' );
//
//				if ( !( $this->_tokenStore instanceof GrantImplicit ) )
//				{
//					throw new ServerException( static::BadRequest, self::Error_UnsupportedGrantType );
//				}
				break;

			//	Extended grant types:
			case filter_var( $_input['grant_type'], FILTER_VALIDATE_URL ):
				if ( !( $this->_tokenStore instanceof GrantExtension ) )
				{
					throw new ServerException( static::BadRequest, self::Error_UnsupportedGrantType );
				}

				$_uri = filter_var( $_input['grant_type'], FILTER_VALIDATE_URL );

				if ( false === ( $_clientDetails = $this->_tokenStore->checkGrantExtension( $_uri, $inputData, $authHeaders ) ) )
				{
					throw new ServerException( static::BadRequest, static::Error_InvalidGrant );
				}
				break;

			default :
				throw new ServerException( static::BadRequest, self::Error_InvalidRequest, 'Invalid grant_type parameter or parameter missing' );
		}

		if ( !isset( $_clientDetails['scope'] ) )
		{
			$_clientDetails['scope'] = null;
		}

		// Check scope, if provided
		if ( $_input['scope'] &&
			 ( !is_array( $_clientDetails ) || !isset( $_clientDetails['scope'] ) || !$this->_checkScope( $_input['scope'], $_clientDetails['scope'] ) )
		)
		{
			throw new ServerException( static::BadRequest, self::Error_InvalidScope, 'An unsupported scope was requested.' );
		}

		$_ownerId = isset( $_clientDetails['user_id'] ) ? $_clientDetails['user_id'] : null;
		$_token = $this->_createAccessToken( $_client[0], $_ownerId, $_clientDetails['scope'] );

		// Send response
		$this->_sendJsonHeaders();

		echo json_encode( $_token );
	}

	/**
	 * Internal function used to get the client credentials from HTTP basic
	 * auth or POST data.
	 *
	 * According to the spec (draft 20), the client_id can be provided in
	 * the Basic Authorization header (recommended) or via GET/POST.
	 *
	 * @param array $inputData
	 * @param array $authHeaders
	 *
	 * @throws \DreamFactory\Exceptions\OAuth\ServerException
	 *
	 * @return array
	 *            A list containing the client identifier and password, for example
	 * @code
	 *            return array(
	 *            CLIENT_ID,
	 *            CLIENT_SECRET
	 *         );
	 * @endcode
	 *
	 * @see       http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-2.4.1
	 */
	protected function _getClientCredentials( array $inputData, array $authHeaders )
	{
		//	Basic Authentication is used
		if ( !empty( $authHeaders['PHP_AUTH_USER'] ) )
		{
			return array( $authHeaders['PHP_AUTH_USER'], $authHeaders['PHP_AUTH_PW'] );
		}
		elseif ( empty( $inputData['client_id'] ) )
		{
			//	No credentials were specified
			throw new ServerException( static::BadRequest, self::Error_InvalidClient, 'Client id was not found in the headers or body' );
		}

		//	This method is not recommended, but is supported by specification
		return array( $inputData['client_id'], $inputData['client_secret'] );
	}

	//.........................................................................
	//. End-user/client Authorization (Section 2 of IETF Draft).
	//.........................................................................

	/**
	 * Pull the authorization request data out of the HTTP request.
	 * - The redirect_uri is OPTIONAL as per draft 20. But your implementation can enforce it
	 * by setting ConfigENFORCE_INPUT_REDIRECT to true.
	 * - The state is OPTIONAL but recommended to enforce CSRF. Draft 21 states, however, that
	 * CSRF protection is MANDATORY. You can enforce this by setting the ConfigENFORCE_STATE to true.
	 *
	 * @param array $inputData - The draft specifies that the parameters should be
	 *                         retrieved from GET, but you can override to whatever method you like.
	 *
	 * @throws \DreamFactory\Exceptions\OAuth\RedirectException
	 * @throws \DreamFactory\Exceptions\OAuth\ServerException
	 * @return mixed The authorization parameters so the authorization server can prompt@see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.1
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-10.12
	 *
	 * @ingroup oauth2_section_3
	 */
	public function getAuthorizeParams( array $inputData = null )
	{
		$_filters = array(
			'client_id'     => array(
				'filter'  => FILTER_VALIDATE_REGEXP,
				'options' => array( 'regexp' => static::RegExpFilter_ClientId ),
				'flags'   => FILTER_REQUIRE_SCALAR
			),
			'response_type' => array( 'flags' => FILTER_REQUIRE_SCALAR ),
			'redirect_uri'  => array( 'filter' => FILTER_SANITIZE_URL ),
			'state'         => array( 'flags' => FILTER_REQUIRE_SCALAR ),
			'scope'         => array( 'flags' => FILTER_REQUIRE_SCALAR )
		);

		if ( !isset( $inputData['redirect_uri'] ) )
		{
			$inputData['redirect_uri'] = null;
		}

		if ( !isset( $inputData ) )
		{
			$inputData = $_GET;
		}

		$_input = filter_var_array( $inputData, $_filters );

		//	Make sure a valid client id was supplied (we can not redirect because we were unable to verify the URI)
		if ( !$_input['client_id'] )
		{
			throw new ServerException( static::BadRequest, self::Error_InvalidClient, 'No client id supplied' );
		}

		if ( false === ( $_clientDetails = $this->_tokenStore->getClientDetails( $_input['client_id'] ) ) )
		{
			throw new ServerException( static::BadRequest, self::Error_InvalidClient, 'Client id does not exist' );
		}

		// Make sure a valid redirect_uri was supplied. If specified, it must match the stored URI.
		// @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-3.1.2
		// @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.2.1
		// @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2.2.1
		if (
			( !$_input['redirect_uri'] && !$_clientDetails['redirect_uri'] ) || ( $this->get( self::ConfigEnforceInputRedirect ) && !$_input['redirect_uri'] )
		)
		{
			throw new ServerException( static::BadRequest, self::Error_RedirectUriMismatch, 'No redirect URI was supplied or stored.' );
		}

		//	Only need to validate if redirect_uri provided on input and stored.
		if ( $_clientDetails['redirect_uri'] && $_input['redirect_uri'] &&
			 !$this->validateRedirectUri( $_input['redirect_uri'], $_clientDetails['redirect_uri'] )
		)
		{
			throw new ServerException( static::BadRequest, self::Error_RedirectUriMismatch, 'The redirect URI provided is missing or does not match' );
		}

		// Select the redirect URI
		$_input['redirect_uri'] = isset( $_input['redirect_uri'] ) ? $_input['redirect_uri'] : $_clientDetails['redirect_uri'];

		// type and client_id are required
		if ( !$_input['response_type'] )
		{
			throw new RedirectException( $_input['redirect_uri'], self::Error_InvalidRequest, 'Invalid or missing response type.', $_input['state'] );
		}

		if ( $_input['response_type'] != self::ResponseTypeAuthCode && $_input['response_type'] != self::ResponseTypeAccessToken )
		{
			throw new RedirectException( $_input['redirect_uri'], self::Error_UnsupportedResponseType, null, $_input['state'] );
		}

		// Validate that the requested scope is supported
		if ( $_input['scope'] && !$this->_checkScope( $_input['scope'], $this->get( self::ConfigSupportedScopes ) ) )
		{
			throw new RedirectException( $_input['redirect_uri'], self::Error_InvalidScope, 'An unsupported scope was requested.', $_input['state'] );
		}

		// Validate state parameter exists (if configured to enforce this)
		if ( $this->get( self::ConfigEnforceState ) && !$_input['state'] )
		{
			throw new RedirectException( $_input['redirect_uri'], self::Error_InvalidRequest, 'The state parameter is required.' );
		}

		// Return retrieved client details together with input
		return ( $_input + $_clientDetails );
	}

	/**
	 * Redirect the user appropriately after approval.
	 *
	 * After the user has approved or denied the access request the
	 * authorization server should call this function to redirect the user
	 * appropriately.
	 *
	 * @param boolean $authorized TRUE or FALSE depending on whether the user authorized the access.
	 * @param int     $ownerId    Identifier of user who authorized the client
	 * @param array   $parameters associative array as below:
	 * - response_type: The requested response: an access token, an
	 *                            authorization code, or both.
	 * - client_id: The client identifier as described in Section 2.
	 * - redirect_uri: An absolute URI to which the authorization server
	 *                            will redirect the user-agent to when the end-user authorization
	 *                            step is completed.
	 * - scope: (optional) The scope of the access request expressed as a
	 *                            list of space-delimited strings.
	 * - state: (optional) An opaque value used by the client to maintain
	 *                            state between the request and callback.
	 *
	 * @see               http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4
	 */
	public function finishClientAuthorization( $authorized, $ownerId = null, $parameters = array() )
	{
		list( $_redirectUri, $_result ) = $this->getAuthResult( $authorized, $ownerId, $parameters );

		$this->_redirectUriCallback( $_redirectUri, $_result );
	}

	/**
	 * @param bool  $authorized
	 * @param int   $ownerId
	 * @param array $parameters
	 *
	 * @return array
	 * @throws \DreamFactory\Exceptions\OAuth\RedirectException
	 */
	public function getAuthResult( $authorized, $ownerId = null, $parameters = array() )
	{
		/**
		 * We repeat this, because we need to re-validate. In theory, this could be POSTed by a 3rd-party (because we are not internally enforcing NONCEs, etc)
		 *
		 * Extracted variables:
		 *
		 * @var string $state
		 * @var string $redirect_uri
		 * @var string $response_type
		 * @var string $client_id
		 * @var string $scope
		 */

		$result = null;

		$parameters = $this->getAuthorizeParams( $parameters );
		$parameters += array( 'scope' => null, 'state' => null );
		extract( $parameters );

		if ( null !== $state )
		{
			$result['query']['state'] = $state;
		}

		if ( false === $authorized )
		{
			throw new RedirectException( $redirect_uri, self::Error_UserDenied, 'The user denied access to your application', $state );
		}
		else
		{
			switch ( $response_type )
			{
				case self::ResponseTypeAuthCode:
					$result['query']['code'] = $this->_createAuthCode( $client_id, $ownerId, $redirect_uri, $scope );
					break;

				case self::ResponseTypeAccessToken:
					$result['fragment'] = $this->_createAccessToken( $client_id, $ownerId, $scope );
					break;
			}
		}

		return array( $redirect_uri, $result );
	}

	//.........................................................................
	//. Other/utility functions.
	//.........................................................................

	/**
	 * Redirect the user agent.
	 *
	 * Handle both redirect for success or error response.
	 *
	 * @param $redirectUri
	 * An absolute URI to which the authorization server will redirect
	 * the user-agent to when the end-user authorization step is completed.
	 * @param $parameters
	 * Parameters to be pass though buildUri().
	 *
	 * @ingroup oauth2_section_4
	 */
	protected function _redirectUriCallback( $redirectUri, $parameters )
	{
		header( 'HTTP/1.1 ' . static::Found );
		header( 'Location: ' . $this->_buildUri( $redirectUri, $parameters ) );
		exit();
	}

	/**
	 * Build the absolute URI based on supplied URI and parameters.
	 *
	 * @param string $uri    An absolute URI.
	 * @param array  $params Parameters to be append as GET.
	 *
	 * @return string An absolute URI with supplied parameters.
	 */
	protected function _buildUri( $uri, $params )
	{
		$_parsed = parse_url( $uri );

		// Add our params to the parsed uri
		foreach ( $params as $_key => $_value )
		{
			if ( isset( $_parsed[$_key] ) )
			{
				$_parsed[$_key] .= '&' . http_build_query( $_value );
			}
			else
			{
				$_parsed[$_key] = http_build_query( $_value );
			}
		}

		// Put humpty dumpty back together
		return
			( ( isset( $_parsed['scheme'] ) ) ? $_parsed['scheme'] . '://' : null )
			. ( ( isset( $_parsed['user'] ) ) ? $_parsed['user'] . ( ( isset( $_parsed['pass'] ) ) ? ':' . $_parsed['pass'] : null ) . '@' : null )
			. ( ( isset( $_parsed['host'] ) ) ? $_parsed['host'] : '' )
			. ( ( isset( $_parsed['port'] ) ) ? ':' . $_parsed['port'] : null )
			. ( ( isset( $_parsed['path'] ) ) ? $_parsed['path'] : null )
			. ( ( isset( $_parsed['query'] ) ) ? '?' . $_parsed['query'] : null )
			. ( ( isset( $_parsed['fragment'] ) ) ? '#' . $_parsed['fragment'] : null );
	}

	/**
	 * Handle the creation of access token, also issue refresh token if support.
	 *
	 * This belongs in a separate factory, but to keep it simple, I'm just
	 * keeping it here.
	 *
	 * @param string $clientId  Client identifier related to the access token.
	 * @param int    $ownerId
	 * @param string $scope     (optional) Scopes to be stored in space-separated string.
	 *
	 * @return array
	 * @see      http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5
	 */
	protected function _createAccessToken( $clientId, $ownerId, $scope = null )
	{
		$_token = array(
			'access_token' => $this->_genAccessToken(),
			'expires_in'   => $this->get( self::ConfigAccessLifetime ),
			'token_type'   => $this->get( static::ConfigTokenType ),
			'scope'        => $scope
		);

		$this->_tokenStore->setAccessToken( $_token['access_token'], $clientId, $ownerId, time() + $this->get( self::ConfigAccessLifetime ), $scope );

		// Issue a refresh token also, if we support them
		if ( $this->_tokenStore instanceof RefreshToken )
		{
			$_token['refresh_token'] = $this->_genAccessToken();

			$this->_tokenStore->setRefreshToken(
				$_token['refresh_token'],
				$clientId,
				$ownerId,
				time() + $this->get( self::ConfigRefreshLifetime ),
				$scope
			);

			// If we've granted a new refresh token, expire the old one
			if ( $this->_oldRefreshToken )
			{
				$this->_tokenStore->unsetRefreshToken( $this->_oldRefreshToken );
				unset( $this->_oldRefreshToken );
			}
		}

		return $_token;
	}

	/**
	 * Handle the creation of auth code.
	 *
	 * This belongs in a separate factory, but to keep it simple, I'm just keeping it here.
	 *
	 * @param string $clientId
	 * @param int    $ownerId
	 * @param string $redirectUri
	 * @param string $scope         (optional) Scopes to be stored in space-separated string.
	 *
	 * @throws \DreamFactory\Exceptions\OAuth\ServerException
	 * @return string
	 */
	protected function _createAuthCode( $clientId, $ownerId, $redirectUri, $scope = null )
	{
		if ( !( $this->_tokenStore instanceOf GrantCode ) )
		{
			throw new ServerException( static::BadRequest, self::Error_UnsupportedGrantType );
		}

		$_code = $this->_genAuthCode();

		$this->_tokenStore->setAuthCode(
			$_code,
			$clientId,
			$ownerId,
			$redirectUri,
			time() + $this->get( self::ConfigAuthLifetime ),
			$scope
		);

		return $_code;
	}

	/**
	 * Generates an unique access token.
	 *
	 * Implementing classes may want to override this function to implement
	 * other access token generation schemes.
	 *
	 * @return An unique access token.
	 * @see OAuth2::genAuthCode()
	 */
	protected function _genAccessToken()
	{
		$_length = 40;

		if ( file_exists( '/dev/urandom' ) )
		{
			// Get 100 bytes of random data
			$_randomData = file_get_contents( '/dev/urandom', false, null, 0, 100 ) . uniqid( mt_rand(), true );
		}
		else
		{
			$_randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime( true ) . uniqid( mt_rand(), true );
		}

		return substr( hash( 'sha512', $_randomData ), 0, $_length );
	}

	/**
	 * Generates an unique auth code.
	 *
	 * Implementing classes may want to override this function to implement
	 * other auth code generation schemes.
	 *
	 * @return string An unique auth code.
	 * @see OAuth2::genAccessToken()
	 */
	protected function _genAuthCode()
	{
		return $this->_genAccessToken(); // let's reuse the same scheme for token generation
	}

	/**
	 * Pull out the Authorization HTTP header and return it.
	 * According to draft 20, standard basic authorization is the only
	 * header variable required (this does not apply to extended grant types).
	 *
	 * Implementing classes may need to override this function if need be.
	 *
	 * @todo We may need to re-implement pulling out apache headers to support extended grant types
	 *
	 * @return array An array of the basic username and password provided.
	 * @see  http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-2.4.1
	 * @ingroup oauth2_section_2
	 */
	protected function _getAuthorizationHeader()
	{
		return array(
			'PHP_AUTH_USER' => FilterInput::server( 'PHP_AUTH_USER' ),
			'PHP_AUTH_PW'   => FilterInput::server( 'PHP_AUTH_PW' ),
		);
	}

	/**
	 * Send out HTTP headers for JSON.
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.1
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
	 *
	 * @ingroup oauth2_section_5
	 */
	protected function _sendJsonHeaders()
	{
		if ( 'cli' != PHP_SAPI && !headers_sent() )
		{
			header( 'Content-Type: application/json' );
			header( 'Cache-Control: no-store' );
		}
	}

	/**
	 * Internal method for validating redirect URI supplied
	 *
	 * @param string $inputUri
	 * @param string $storedUri
	 *
	 * @return bool
	 */
	protected function validateRedirectUri( $inputUri, $storedUri )
	{
		if ( !$inputUri || !$storedUri )
		{
			return false; // if either one is missing, assume INVALID
		}

		return 0 === strcasecmp( substr( $inputUri, 0, strlen( $storedUri ) ), $storedUri );
	}

	/**
	 * Default configuration options are specified here.
	 */
	protected function _setDefaultOptions()
	{
		$this->merge(
			array(
				 self::ConfigAccessLifetime       => self::DefaultAccessTokenLifetime,
				 self::ConfigRefreshLifetime      => self::DefaultRefreshTokenLifetime,
				 self::ConfigAuthLifetime         => self::DefaultAuthCodeLifetime,
				 self::ConfigRealm                => self::DefaultRealm,
				 self::ConfigTokenType            => self::TokenTypeBearer,
				 self::ConfigEnforceInputRedirect => false,
				 self::ConfigEnforceState         => false,
				 // This is expected to be passed in on construction. Scopes can be an arbitrary string.
				 self::ConfigSupportedScopes      => array(),
			)
		);

	}

}

