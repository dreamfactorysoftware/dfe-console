<?php
namespace Cerberus\Interfaces;

/**
 * ProvisionerFeatures
 * Provisioning constants
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 */
interface ProvisionerFeatures
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const VirtualMachines = 'vm';
	/**
	 * @var string
	 */
	const BlobStorage = 'blob';
	/**
	 * @var string
	 */
	const SqlStorage = 'sql';
	/**
	 * @var string
	 */
	const BlockStorage = 'block';
	/**
	 * @var string
	 */
	const Smtp = 'smtp';
	/**
	 * @var string
	 */
	const Dns = 'dns';
}
