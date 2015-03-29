<?php
namespace Cerberus\Services\Provisioning;

use Cerberus\Interfaces\ProvisionerLike;
use Cerberus\Interfaces\VirtualServerLike;
use Cerberus\Services\Provisioning\DreamFactory\HostedInstance;
use Cerberus\Yii\Models\Auth\User;
use Cerberus\Yii\Models\Deploy\Instance;
use DreamFactory\Services\DreamService;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Exceptions\NotImplementedException;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\Log;

/**
 * BaseProvisioner
 * Base class for provisioning services
 *
 * @author        Jerry Ablan <jerryablan@dreamfactory.com>
 */
abstract class BaseProvisioner extends DreamService implements ProvisionerLike, VirtualServerLike, ConsumerLike
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const HostPattern = "/^([a-zA-Z0-9])+$/";
	/**
	 * @var string
	 */
	const CharacterPattern = '/[^a-zA-Z0-9]/';

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param int   $location
	 * @param User  $user
	 * @param array $settings
	 *
	 * @throws \Kisma\Core\Exceptions\NotImplementedException
	 * @return mixed
	 */
	public static function getService( $location, $user, $settings = array() )
	{
		switch ( $location )
		{
			case Instance::AMAZON_HOSTED:
				return new Amazon( $user, $settings );

			case Instance::FABRIC_HOSTED:
				return new HostedInstance( $user, $settings );

			case Instance::AZURE_HOSTED: //	@todo Implement
			case Instance::RACKSPACE_HOSTED: //	@todo Implement
				//	Not implemented yet
			default:
				throw new NotImplementedException();
		}
	}
}