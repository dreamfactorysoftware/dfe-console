<?php
namespace Cerberus\Interfaces;

/**
 * Provisioners
 * Provisioning constants
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 */
interface Provisioners
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var int
	 */
	const Amazon = 0;
	/**
	 * @var int
	 */
	const DreamFactory = 1;
	/**
	 * @var int
	 */
	const Azure = 2;
	/**
	 * @var int
	 */
	const Rackspace = 3;
	/**
	 * @var int
	 */
	const OpenStack = 4;
}
