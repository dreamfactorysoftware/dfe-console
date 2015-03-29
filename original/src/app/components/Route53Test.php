<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jablan
 * Date: 2/27/13
 * Time: 10:28 AM
 * To change this template use File | Settings | File Templates.
 */

namespace DreamFactory;

use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\Log;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Class Route53Test
 *
 * @package DreamFactory
 */
class Route53Test extends \PHPUnit_Framework_TestCase implements ConsumerLike
{
	const TEST_HOST_NAME = '__route53__unit__test__.cloud.dreamfactory.com';

	/**
	 * @var \Route53
	 */
	protected $_service;

	protected function tearDown()
	{
		$this->_service = null;
	}

	protected function setUp()
	{
		Log::setDefaultLog( '/opt/dreamfactory/log/testing.log' );
		$this->_service = new \Route53( $this, require( __DIR__ . '/../../../config/amazon.api-user.keys.php' ) );
	}

	/**
	 * @return bool
	 * @covers \Route53::changeResourceRecordsets
	 */
	public function testChangeResourceRecordsets()
	{
		$this->assertTrue(
			$this->_service->changeResourceRecordsets(
				'cloud',
				static::TEST_HOST_NAME,
				'127.0.0.1',
				'A',
				3600,
				'Created by unit tester'
			)
		);

		sleep( 5 );

		//	Try to look up the new name
		$_dns = dns_get_record( static::TEST_HOST_NAME );
		Log::debug( print_r( $_dns, true ) );

		$this->assertNotEmpty(
			$_dns
		);

		//	Delete the record...
		$this->assertTrue(
			$this->_service->changeResourceRecordsets(
				'cloud',
				static::TEST_HOST_NAME,
				'127.0.0.1',
				'A',
				3600,
				'Deleted by unit tester',
				true
			)
		);
	}

}
