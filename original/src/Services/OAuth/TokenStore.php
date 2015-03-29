<?php
/**
 * @file
 */
namespace Cerberus\Services\OAuth;
use DreamFactory\Interfaces\OAuth\Client;
use DreamFactory\Interfaces\OAuth\GrantCode;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\SeedBag;
use Kisma\Core\Services\SeedService;
use Kisma\Core\Utility\Hasher;
use Kisma\Core\Utility\Log;

/**
 * TokenStore
 */
class TokenStore extends SeedBag implements GrantCode
{
	/**
	 * Add a new client to the database.
	 *
	 * @param int    $ownerId
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param string $redirectUri
	 *
	 * @return bool
	 */
	public function addClient( $ownerId, $clientId, $clientSecret, $redirectUri )
	{
		try
		{
			$_model = new \AuthClient();
			$_model->setAttributes(
				array(
					 'owner_id'           => $ownerId,
					 'client_id_text'     => $clientId,
					 'client_secret_text' => Hasher::encryptString( $clientSecret, Pii::getParam( 'oauth.salt' ) ),
					 'redirect_uri_text'  => $redirectUri,
				)
			);

			$_model->save();

			return $_model->id;
		}
		catch ( \CDbException $_ex )
		{
			Log::error( 'Exception adding client: ' . $_ex->getMessage() );

			return false;
		}
	}

	/**
	 * @param string $clientId
	 * @param string $clientSecret
	 *
	 * @return bool
	 */
	public function checkClientCredentials( $clientId, $clientSecret = null )
	{
		try
		{
			if ( null !== ( $_client = \AuthClient::model()->findByPk( $clientId ) ) )
			{
				if ( null === $clientSecret )
				{
					return true;
				}

				return Hasher::decryptString( $clientSecret, Pii::getParam( 'oauth.salt' ) ) == $_client->client_secret_text;
			}
		}
		catch ( \Exception $_ex )
		{
			Log::error( 'Exception: ' . $_ex->getMessage() );
		}

		return false;
	}

	/**
	 * @param string $clientId
	 *
	 * @return bool
	 */
	public function getClientDetails( $clientId )
	{
		if ( null === ( $_client = \AuthClient::model()->findByPk( $clientId ) ) )
		{
			return false;
		}

		return array(
			'client_id'    => $_client->client_id_text,
			'owner_id'     => $_client->owner_id,
			'redirect_uri' => $_client->redirect_uri_text,
		);
	}

	/**
	 * @param string $token
	 *
	 * @return mixed
	 */
	public function getAccessToken( $token )
	{
		return $this->_getToken( $token, false );
	}

	/**
	 * @param      $oauthToken
	 * @param      $clientId
	 * @param      $ownerId
	 * @param      $expireDate
	 * @param null $scope
	 */
	public function setAccessToken( $oauthToken, $clientId, $ownerId, $expireDate, $scope = null )
	{
		$this->_setToken( $oauthToken, $clientId, $ownerId, $expireDate, $scope, false );
	}

	/**
	 * @param string $refreshToken
	 *
	 * @return mixed
	 */
	public function getRefreshToken( $refreshToken )
	{
		return $this->_getToken( $refreshToken, true );
	}

	/**
	 * @param string $refreshToken
	 * @param string $clientId
	 * @param int    $ownerId
	 * @param int    $expireDate
	 * @param string $scope
	 *
	 * @return mixed
	 */
	public function setRefreshToken( $refreshToken, $clientId, $ownerId, $expireDate, $scope = null )
	{
		return $this->_setToken( $refreshToken, $clientId, $ownerId, $expireDate, $scope, true );
	}

	/**
	 * @param $token
	 *
	 * @return mixed
	 */
	public function unsetRefreshToken( $token )
	{
		return \AuthToken::model()->findByPk( $token )->delete();
	}

	/**
	 * @param $code
	 *
	 * @return null
	 */
	public function getAuthCode( $code )
	{
		if ( null === ( $_code = \AuthCode::model()->findByPk( $code ) ) )
		{
			return null;
		}

		return $_code->getAttributes();
	}

	/**
	 * @param string $code
	 * @param string $clientId
	 * @param int    $ownerId
	 * @param string $redirectUri
	 * @param int    $expireDate
	 * @param null   $scope
	 *
	 * @return bool
	 */
	public function setAuthCode( $code, $clientId, $ownerId, $redirectUri, $expireDate, $scope = null )
	{
		try
		{
			$_model = new \AuthCode();
			$_model->setAttributes(
				array(
					 'owner_id'          => $ownerId,
					 'code_text'         => $code,
					 'client_id_text'    => $clientId,
					 'user_id'           => $ownerId,
					 'redirect_uri_text' => $redirectUri,
					 'expire_date'       => $expireDate,
					 'scope_text'        => $scope,
				)
			);

			return $_model->save();
		}
		catch ( \CDbException $_ex )
		{
			Log::error( 'Exception: ' . $_ex->getMessage() );
		}

		return false;
	}

	/**
	 * @param string $clientId
	 * @param string $grantType
	 *
	 * @return bool
	 */
	public function checkRestrictedGrantType( $clientId, $grantType )
	{
		return true; // Not implemented
	}

	/**
	 * @param string $token
	 * @param string $clientId
	 * @param int    $ownerId
	 * @param int    $expireDate
	 * @param string $scope
	 * @param bool   $isRefresh
	 *
	 * @return bool
	 */
	protected function _setToken( $token, $clientId, $ownerId, $expireDate, $scope, $isRefresh = false )
	{
		try
		{
			$_token = new \AuthToken();
			$_token->token_text = $token;
			$_token->refresh_ind = ( $isRefresh ? 1 : 0 );
			$_token->client_id_text = $clientId;
			$_token->owner_id = $ownerId;
			$_token->expire_date = $expireDate;
			$_token->scope_text = $scope;

			return $_token->save();
		}
		catch ( \CDbException $_ex )
		{
			Log::error( 'Exception: ' . $_ex->getMessage() );

			return false;
		}
	}

	/**
	 * @param string $token
	 * @param bool   $isRefresh
	 *
	 * @return array|null
	 */
	protected function _getToken( $token, $isRefresh = true )
	{
		$_token = \AuthToken::model()->find(
			'token_text = :token_text and refresh_ind = :refresh_ind',
			array(
				 ':token_text'  => $token,
				 ':refresh_ind' => ( $isRefresh ? 1 : 0 ),
			)
		);

		if ( null === $_token )
		{
			return null;
		}

		return $_token->getAttributes();
	}
}
