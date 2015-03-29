<?php
/**
 * ProfileController.php
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
/**
 * ProfileController class
 * This default controller
 */
class ProfileController extends \DreamFactory\Yii\Controllers\DreamController
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
			self::Authenticated,
			array(
				 'index',
				 'configure',
			)
		);
	}

	/**
	 *
	 */
	public function actionConfigure( $id = null )
	{
		if ( null === $id )
		{
			$id = \Kisma\Core\Utility\FilterInput::request( $id, FILTER_SANITIZE_NUMBER_INT );
		}

		$_model = null;

		if ( null !== $id )
		{
			$_model = ServiceConfig::model()->findByPk( $id );
		}

		if ( null === $_model )
		{
			$_model = new ServiceConfig();
		}

		$this->render(
			'configure',
			array(
				 'model'  => $_model,
				 'update' => !$_model->getIsNewRecord(),
			)
		);
	}

	/**
	 * Test Action
	 *
	 * @return void
	 */
	public function actionIndex()
	{
		$this->render( 'index' );
	}
}
