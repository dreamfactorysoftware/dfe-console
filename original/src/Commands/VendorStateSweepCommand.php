<?php
namespace Cerberus\Commands;
use Cerberus\Yii\Models\Deploy\Instance;
use DreamFactory\Yii\Commands\CliProcess;

/**
 * VendorStateSweepCommand
 * Sweeps vendors for instance states to keep local db in sync
 */
class VendorStateSweepCommand extends CliProcess
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 *
	 * @param string $credentials A path to a file containing credentials
	 * @param string $instanceId
	 * @param bool   $verbose     Verbose mode
	 *
	 * @return void
	 */
	public function actionAmazon( $credentials = null, $instanceId = null, $verbose = false )
	{
		$this->_authorize( $credentials );
		$this->_refreshAmazon( $instanceId, $verbose );
	}

	/**
	 * @param string $credentials A path to a file containing credentials
	 */
	protected function _authorize( $credentials = null )
	{
		$_file = $credentials ? : \Kisma::get( 'app.config_path' ) . '/amazon.api-user.keys.php';
		\CFCredentials::set( @include( $_file ) );

	}

	/**
	 * @param string $instanceId
	 * @param bool   $verbose     Verbose mode
	 */
	protected function _refreshAmazon( $instanceId = null, $verbose = false )
	{
		$_update = 0;

		$this->logInfo( '>> Begin Amazon refresh' );

		$_service = new \AmazonEC2();

		//	1.	Refresh instances
		$this->logDebug( '  -- Getting instances' );

		$_response = $_service->describe_instances( null !== $instanceId ? array( 'InstanceId' => $instanceId ) : array() );

		if ( $_response->isOk() )
		{
			foreach ( $_response->body->reservationSet->item as $_reservation )
			{
				$_instance = $_reservation->instancesSet->item;

				/** @var $_model \Instance */
				$_model = Instance::model()->findByAttributes( array( 'instance_id_text' => (string)$_instance->instanceId ) );

				if ( null === $_model )
				{
					continue;
				}

				$_model->update(
					array(
						 'vendor_state_nbr'  => $_instance->instanceState->code,
						 'vendor_state_text' => $_instance->instanceState->name,
					)
				);

				unset( $_instance, $_model );
				$_update++;
			}
		}

		unset( $_response );

		$this->logInfo( '<< Complete < Amazon refresh < Updated ' . $_update . ' instance(s).' );
	}

}