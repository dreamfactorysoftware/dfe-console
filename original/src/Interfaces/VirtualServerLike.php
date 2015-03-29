<?php
namespace Cerberus\Interfaces;

/**
 * VirtualServerLike
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 */
interface VirtualServerLike
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param mixed $request
	 *
	 * @return mixed
	 */
	public function start( $request );

	/**
	 * @param mixed $request
	 *
	 * @return mixed
	 */
	public function stop( $request );

	/**
	 * @param mixed $request
	 *
	 * @return mixed
	 */
	public function terminate( $request );
}
