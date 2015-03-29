<?php
namespace Cerberus\Commands;

use Cerberus\Services\Provisioning\Route53;
use DreamFactory\Yii\Commands\BaseQueueServicingCommand;
use Kisma\Core\Utility\Log;

/**
 * ManageDnsCommand
 * Manages Route 53 DNS requests
 */
class ManageDnsCommand extends BaseQueueServicingCommand
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const Endpoint = 'https://route53.amazonaws.com/2012-02-29';

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param string $zone
	 * @param string $entry
	 * @param string $type
	 * @param string $value
	 * @param int    $ttl
	 *
	 * @return bool
	 */
	public function actionDelete( $zone, $entry, $type, $value, $ttl = 300 )
	{
		$_service = new Route53( $this );

		return $_service->changeResourceRecordsets(
			$zone,
			rtrim( $entry, '. ' ) . '.',
			$value,
			strtoupper( $type ),
			$ttl,
			'Deleted with manageDns CLI command',
			true
		);
	}

	/**
	 * @param string $zone
	 * @param string $entry
	 * @param string $type
	 * @param string $value
	 * @param int    $ttl
	 *
	 * @return bool
	 */
	public function actionCreate( $zone, $entry, $type, $value, $ttl = 300 )
	{
		$_service = new Route53( $this );

		return $_service->changeResourceRecordsets(
			$zone,
			rtrim( $entry, '. ' ) . '.',
			$value,
			strtoupper( $type ),
			$ttl,
			'Created with manageDns CLI command'
		);
	}
}