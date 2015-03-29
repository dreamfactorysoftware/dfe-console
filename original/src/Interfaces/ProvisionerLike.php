<?php
namespace Cerberus\Interfaces;
/**
 * ProvisionerLike
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 */
interface ProvisionerLike
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param mixed $request
	 *
	 * @return mixed
	 */
	public function provision( $request );

	/**
	 * @param mixed $request
	 *
	 * @return mixed
	 */
	public function deprovision( $request );
}
