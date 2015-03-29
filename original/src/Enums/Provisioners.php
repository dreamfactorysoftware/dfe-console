<?php
namespace Cerberus\Enums;

use Cerberus\Services\Provisioning\BaseProvisioner;
use Kisma\Core\Enums\SeedEnum;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\Option;

/**
 * Provisioners
 * Provisioning class mapper
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 */
class Provisioners extends SeedEnum implements \Cerberus\Interfaces\Provisioners
{
	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * @var array
	 */
	protected static $_classMap
		= array(
			self::Amazon       => 'Cerberus\\Services\\Provisioning\\Amazon',
			self::DreamFactory => 'Cerberus\\Services\\Provisioning\\DreamFactory',
			self::Azure        => 'Cerberus\\Services\\Provisioning\\Azure',
			self::Rackspace    => 'Cerberus\\Services\\Provisioning\\Rackspace',
			self::OpenStack    => 'Cerberus\\Services\\Provisioning\\OpenStack',
		);

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param int                                 $which
	 * @param \Kisma\Core\Interfaces\ConsumerLike $consumer
	 * @param array                               $settings
	 *
	 * @throws \InvalidArgumentException
	 * @return BaseProvisioner
	 */
	public static function getService( $which, ConsumerLike $consumer, $settings = array() )
	{
		if ( !static::contains( $which ) )
		{
			throw new \InvalidArgumentException( 'The service "' . $which . '" is not valid.' );
		}

		$_class = Option::get( self::$_classMap, $which );

		return new $_class( $consumer, $settings );
	}
}
