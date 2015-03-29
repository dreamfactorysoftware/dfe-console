<?php
/**
 * RestServiceController.php
 */
/**
 * RestServiceController
 */
class RestServiceController extends BaseServiceController
{
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

		//	Set our access rules..
		$this->addUserActions(
			self::Any,
			array(
				'test',
			)
		);
	}

	/**
	 * @param string $command
	 *
	 * @return string
	 */
	public function requestTest( $command = 'login' )
	{
		$_api = new \DirectConnect\ServiceHandlers\ServiceHandler();
		$_user = new \DirectConnect\Services\Web\User( $_api );
		$_result = $_user->handleRestRequest( $command );

		//	Return results
		return $this->_createResponse( $_result );
	}

}