<?php
/**
 * InstanceStates.php
 */
/**
 * InstanceStates
 */
interface InstanceStates
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var int
	 */
	const Created = 0;
	/**
	 * @var int
	 */
	const Provisioning = 1;
	/**
	 * @var int
	 */
	const Provisioned = 2;
	/**
	 * @var int
	 */
	const Deprovisioning = 3;
	/**
	 * @var int
	 */
	const Deprovisioned = 4;
	/**
	 * @var int
	 */
	const CreationError = 10;
	/**
	 * @var int
	 */
	const ProvisioningError = 12;
	/**
	 * @var int
	 */
	const DeprovisioningError = 14;
}
