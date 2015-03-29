<?php
use Cerberus\Services\GraylogData;
use Cerberus\Yii\Models\Drupal\Users;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Hasher;
use Kisma\Core\Utility\Log;

/**
 * WebController.php
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */

/**
 * WebController class
 * This default controller
 */
class WebController extends \DreamFactory\Yii\Controllers\DreamController
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

		//	We want merged update/create...
		$this->setSingleViewMode( true );
		$this->defaultAction = 'index';
		$this->setLoginFormClass( 'DreamServerLoginForm' );

		$this->layout = 'main';

		//	Anyone can see the index
		$this->addUserActions(
			self::Guest,
			array(
				'login',
				'register',
				'recover',
				'users',
			)
		);

		//	Everything else is auth-required
		$this->addUserActions(
			self::Authenticated,
			array(
				'index',
				'profile',
				'usage',
				'logout',
				'users',
			)
		);
	}

	public function actionUsers()
	{
		$_model = Users::model()->byEmailAddress( 'jerryablan@dreamfactory.com' )->find();

		if ( null !== $_model )
		{
			print_r( $_model->getAttributes() );
		}
		else
		{
			echo 'NO SIR';
		}

		Pii::end();
	}

	/**
	 * Overridden login to set layout
	 */
	public function actionLogin()
	{
		$this->layout = 'login';

		parent::actionLogin();
	}
}

