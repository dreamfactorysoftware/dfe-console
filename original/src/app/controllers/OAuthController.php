<?php
/**
 * OAuthController.php
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
use Cerberus\Services\OAuth\Server;
use Cerberus\Services\OAuth\TokenStore;
use DreamFactory\Exceptions\OAuth\ServerException;
use DreamFactory\Yii\Controllers\DreamController;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\FilterInput;
use \Kisma\Core\Utility\Hasher;

/**
 * OAuthController class
 * This default controller
 */
class OAuthController extends DreamController
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize the controller
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();

		$this->layout = false;

		//	Anyone can see the index
		$this->addUserActions(
			self::Any,
			array(
				'index',
				'test'
			)
		);

		//	Everything else is auth-required
		$this->addUserActions(
			self::Authenticated,
			array(
				'authorize',
				'client',
				'secret',
				'secretFail',
			)
		);
	}

	public function actionSecret()
	{
		$this->oauthFilter();
		$this->render( 'secret' );
	}

	public function actionSecretFail()
	{
		$this->render( 'secretFail' );
	}

	/**
	 * access filter for secret page
	 */
	public function oauthFilter()
	{
		try
		{
			$_server = new Server(
				new TokenStore()
			);

			$_token = $_server->getBearerToken();
			$_server->verifyAccessToken( $_token );
		}
		catch ( ServerException $_ex )
		{
			$_ex->sendHttpResponse();
		}
	}

	/**
	 *
	 */
	public function actionClient()
	{
		$_clientId = 's34c934hdsjtds9v20gtr5l';
		$_clientSecret = 'P/92i4oNutMOawl76/bQGz/bsMwk8K25uCNOmiyuICVhGgqfxY5UjHketyCVtZuDUMBSFtS5Kvcq3CmhCe8/zQ==';
		$_redirectUri = 'http://dfab001.cloud.dreamfactory.com/oauth/authorize';

		$_store = new TokenStore();
		$_store->addClient( Pii::user()->id, $_clientId, $_clientSecret, $_redirectUri );
	}

	/**
	 * Authorize a token
	 */
	public function actionAuthorize()
	{
		//	Click-jacking prevention (supported by IE8+, FF3.6.9+, Opera10.5+, Safari4+, Chrome 4.1.249.1042+)
		header( 'X-Frame-Options: DENY' );

		$_server = new Server(
			new TokenStore()
		);

		$_clientId = FilterInput::request( 'client_id', 's34c934hdsjtds9v20gtr5l' );

		if ( null === ( $_client = AuthClient::model()->findByPk( $_clientId ) ) )
		{
			throw new ServerException( 401, 'Not authorized.' );
		}

		if ( Pii::postRequest() )
		{
			$_server->finishClientAuthorization( true, $_client->owner_id, $_POST );
		}
		else
		{
			$_redirectUri = $_client->redirect_uri_text;
			$_responseType = FilterInput::request( 'response_type', 'code' );

			try
			{
				$_authParams = $_server->getAuthorizeParams(
					array(
						'client_id'     => $_clientId,
						'redirect_uri'  => $_redirectUri,
						'response_type' => $_responseType,
					)
				);

				$this->render(
					'authorize',
					array(
						'authParams' => $_authParams,
					)
				);
			}
			catch ( ServerException $_ex )
			{
				$_ex->sendHttpResponse();
			}
		}
	}
}
