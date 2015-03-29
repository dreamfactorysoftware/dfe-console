<?php
use DreamFactory\Yii\Utility\Pii;

/**
 * ServicesController.php
 *
 * @link         http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author       Jerry Ablan <jerryablan@dreamfactory.com>
 */

/**
 * ServicesController class
 * This controller provides access to the service management functions
 */
class ServicesController extends \DreamFactory\Yii\Controllers\DreamController
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string Seasoning
	 */
	const SaltyGoodness = 'salty.tip!';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize the controller
	 */
	public function init()
	{
		parent::init();

		$this->setModelName( 'Service' );
		$this->defaultAction = 'admin';
		$this->setSingleViewMode( true );

		$this->addUserAction(
			self::Any,
			'index'
		);

		$this->addUserActions(
			self::Authenticated,
			array(
				 'admin',
				 'variables',
				 'statistics',
				 'addVariable',
				 'updateVariable',
			)
		);
	}

	/**
	 * @param string $serviceId
	 *
	 * @return void
	 */
	public function actionStatistics( $serviceId = null )
	{
		$_model = new Service();
		$_model->unsetAttributes();

		if ( isset( $_REQUEST['Service'] ) )
		{
			$_model->attributes = $_REQUEST['Service'];
		}

		$this->render(
			'statistics',
			array(
				 'model'   => $_model,
				 'service' => $serviceId,
			)
		);
	}

	public function getServiceVariables( $service )
	{
		$_result = array();

		if ( $service->variables )
		{
			/** @var $_variable ServiceVariable */
			foreach ( $service->variables as $_variable )
			{
				$_result[$_variable->id] = $_variable->getAttributes();
				unset( $_variable );
			}
		}

		return $_result;
	}

	/**
	 * @param int $serviceId
	 *
	 * @throws CHttpException
	 */
	public function actionVariables( $serviceId )
	{
		/** @var $_service Service */
		if ( null === ( $_service = Service::model()->findByPk( filter_var( $serviceId, FILTER_SANITIZE_NUMBER_INT ) ) ) )
		{
			throw new CHttpException( 404, 'Invalid service specified.' );
		}

		$this->render(
			'_service_variables',
			array(
				 'title'     => 'Service Variables',
				 'header'    => 'Service Variables',
				 'variables' => $this->getServiceVariables( $_service ),
				 'service'   => $_service,
				 'update'    => true,
			)
		);
	}

	/**
	 * @param int $serviceId
	 *
	 * @throws CHttpException
	 */
	public function actionUpdateVariable( $serviceId )
	{
		if ( null === ( $_payload = \Kisma\Core\Utility\FilterInput::post( 'id', null ) ) )
		{
			throw new CHttpException( 403, 'Invalid request.', 0 );
		}

		$_payload = \Kisma\Core\Utility\Hasher::decryptString( $_payload, self::SaltyGoodness, true );

		if ( false === ( $_params = json_decode( $_payload ) ) )
		{
			throw new CHttpException( 404, 'Service not found.' . print_r( $_params, true ) );
		}

		if ( $serviceId !== $_params->serviceId )
		{
			throw new CHttpException( 404, 'Service not found.' . print_r( $_params, true ) );
		}

		\Kisma\Core\Utility\Log::debug( 'Params: ' . print_r( $_params, true ) );

		if ( !isset( $_params->variable, $_params->variable->id ) )
		{
			throw new CHttpException( 404, 'Variable not found.' );
		}

		$_id = $_params->variable->id;
		$_delete = ( 1 == \Kisma\Core\Utility\FilterInput::post( 'delete', 0 ) );

		$this->layout = false;

		\Kisma\Core\Utility\Log::debug( 'Got update for variable id: ' . $_id );

		/** @var $_variable ServiceVariable */
		$_variable = ServiceVariable::model()->find(
			'service_id = :service_id and id = :id',
			array(
				 ':service_id' => $_params->serviceId,
				 ':id'         => $_id,
			)
		);

		if ( null === $_variable )
		{
			throw new CHttpException( 403, 'Invalid request.', 1 );
		}

		$_column = strtolower( \Kisma\Core\Utility\FilterInput::post( 'columnName' ) );
		$_newValue = \Kisma\Core\Utility\FilterInput::post( 'value', $_variable->value_text );

		if ( $_delete )
		{
			\Kisma\Core\Utility\Log::info( 'Deleting service variable "' . $_variable->name_text . '"' );

			return $_variable->delete() ? 1 : 0;
		}

		\Kisma\Core\Utility\Log::info( 'Replacing service variable "' . $_variable->name_text . '"\'s ' . $_column . ' column with: ' . $_newValue );

		if ( in_array( $_column, array( 'name', 'value' ) ) )
		{
			$_variable->setAttribute( $_column . '_text', $_newValue );
		}

		if ( !$_variable->save() )
		{
			throw new CHttpException( 500, 'Unable to save change: ' . $_variable->getErrorsForLogging() );
		}

		$_variable->refresh();

		echo $_variable->getAttribute( $_column . '_text' );
	}

	/**
	 * @throws CHttpException
	 */
	public function actionAddVariable()
	{
		if ( null === ( $_serviceId = \Kisma\Core\Utility\FilterInput::post( 'service_id', null ) ) )
		{
			throw new CHttpException( 403, 'Invalid request.', 0 );
		}

		if ( null === ( $_service = Service::model()->findByPk( $_serviceId ) ) )
		{
			throw new CHttpException( 404, 'Service not found.' );
		}

		if ( null === ( $_name = \Kisma\Core\Utility\FilterInput::post( 'name_text', null ) ) )
		{
			throw new CHttpException( 403, 'Invalid request.', 0 );
		}

		$_value = \Kisma\Core\Utility\FilterInput::post( 'value_text', null );

		$_model = new ServiceVariable();
		$_model->service_id = $_serviceId;
		$_model->name_text = $_name;
		$_model->value_text = $_value;

		try
		{
			if ( !$_model->save() )
			{
				throw new CDbException( $_model->getErrorsForLogging() );
			}

			Pii::setFlash( 'success', 'Your new variable "' . $_name . '" has been created.' );
		}
		catch ( Exception $_ex )
		{
			Pii::setFlash( 'failure', 'Your new variable "' . $_name . '" could not be saved.' );
		}

		$this->redirect( '/services/variables/serviceId/' . $_serviceId . '/' );
	}
}
