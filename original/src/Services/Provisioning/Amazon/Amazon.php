<?php
namespace Cerberus\Services\Provisioning;

use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Exceptions\ServiceException;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;
use Kisma\Core\Utility\Sql;

/**
 * Amazon
 * EC2 provisioning service
 *
 * @author        Jerry Ablan <jerryablan@dreamfactory.com>
 */
class Amazon extends BaseProvisioner
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param \Kisma\Core\Interfaces\ConsumerLike  $consumer
	 * @param array                                $settings
	 */
	public function __construct( ConsumerLike $consumer, $settings = array() )
	{
		$this->_client = new \AmazonEC2( $settings );

		parent::__construct( $consumer, $settings );
	}

	/**
	 * Creates a hosted instance
	 *
	 * @param array $request
	 *
	 * @throws \Kisma\Core\Exceptions\ServiceException
	 * @return bool|\CFResponse|mixed
	 */
	public function provision( $request )
	{
		$_ids = $this->_getIds( $request );
		$_min = Option::get( $request, 'minimum', 1, true );
		$_max = Option::get( $request, 'maximum', 1, true );

		//	Key and security groups
		if ( null === Option::get( $request, 'key_name' ) )
		{
			$request['KeyName'] = Option::get( $this->_settings, 'default_key_name', 'dfadmin' );
		}

		if ( null === Option::get( $request, 'security_group' ) && null === ( $_secGroupId = Option::get( $request, 'security_group_id' ) ) )
		{
			//	Default to dsp-basic security
			$request['SecurityGroup'] = Option::get( $this->_settings, 'default_security_group', 'dsp-basic' );
		}

		//	And the size...
		if ( null === Option::get( $request, 'instance_type' ) )
		{
			//	Default to dsp-basic security
			$request['InstanceType'] = Option::get( $this->_settings, 'default_instance_type', 't1.micro' );
		}

		$_response = $this->_client->run_instances( $_ids, $_min, $_max, $request );

		if ( isset( $_response->Error ) )
		{
			$this->logError( 'Error requesting instance provisioning: ' . (string)$_response->Error->Message );

			return false;
		}

		return $_response;
	}

	/**
	 * @param mixed $request
	 *
	 * @throws \Kisma\Core\Exceptions\ServiceException
	 * @return \CFResponse
	 */
	public function deprovision( $request )
	{
		$_ids = $this->_getIds( $request );
		$_response = $this->_client->terminate_instances( $_ids, $request );

		if ( isset( $_response->Error ) )
		{
			$this->logError( 'Error requesting instance deprovisioning: ' . (string)$_response->Error->Message );

			return false;
		}

		return true;
	}

	/**
	 * @param mixed $request
	 *
	 * @return bool|\CFResponse|mixed
	 * @throws \Kisma\Core\Exceptions\ServiceException
	 */
	public function start( $request )
	{
		$_ids = $this->_getIds( $request );

		$_response = $this->_client->start_instances( $_ids, $request );

		if ( isset( $_response->Error ) )
		{
			$this->logError( 'Error requesting instance provisioning: ' . (string)$_response->Error->Message );

			return false;
		}

		return $_response;
	}

	/**
	 * @param mixed $request
	 *
	 * @throws \Kisma\Core\Exceptions\ServiceException
	 * @return bool|\CFResponse|mixed
	 */
	public function stop( $request )
	{
		$_ids = $this->_getIds( $request );

		$_response = $this->_client->stop_instances( $_ids, $request );

		if ( isset( $_response->Error ) )
		{
			$this->logError( 'Error requesting to stop instance(s): ' . (string)$_response->Error->Message );

			return false;
		}

		return $_response;
	}

	/**
	 * @param mixed $request
	 *
	 * @return bool|\CFResponse|mixed
	 */
	public function terminate( $request )
	{
		return $this->deprovision( $request );
	}

	/**
	 * @param string|array $request
	 *
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	protected function _getIds( &$request )
	{
		if ( is_string( $request ) )
		{
			$request = array( 'ids' => $request );
		}

		if ( null === ( $_ids = Option::get( $request, 'ids', null, true ) ) )
		{
			throw new \InvalidArgumentException( 'You must specify the "ids" of the instance(s) that you wish to stop.' );
		}

		return $_ids;
	}
}