<?php
namespace Cerberus\Yii\Utility;

/**
 * DspManager.php
 *
 * @copyright Copyright (c) 2012-2013 DreamFactory Software, Inc.
 * @link      http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 *
 * @filesource
 */
use DreamFactory\Services\DreamService;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\FilterInput;

/**
 * DspManager
 * Manages DSPs
 */
class DspManager extends DreamService implements ConsumerLike
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const HostPattern = "/^([a-z]|[A-Z]|[0-9]|\-){6,61}+$/";

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param string $name
	 *
	 * @return int
	 */
	public static function validateHostName( $name )
	{
		static $_unavailableNames = array();

		if ( null === $_unavailableNames )
		{
			$_unavailableNames = require_once( \Kisma::get( 'app.config_path' ) . '/unavailable_names.config.php' );
		}

		//	Check host name
		return preg_match( static::HostPattern, $name );
	}

	/**
	 * Checks if a user can start a trial
	 *
	 * @param User $user
	 *
	 * @return bool FALSE if user cannot start a trial, otherwise true
	 */
	public static function validateTrial( $user )
	{
		//	Can user launch a trial?
		if ( $user->instances )
		{
			/** @var $_instance Instance */
			foreach ( $user->instances as $_instance )
			{
				if ( 1 == $_instance->trial_instance_ind )
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks if a user can start a trial
	 *
	 * @param User $user
	 *
	 * @return bool FALSE if user has no trial instance, otherwise the instance
	 */
	public static function trialInstance( $user )
	{
		//	Can user launch a trial?
		if ( $user->instances )
		{
			/** @var $_instance Instance */
			foreach ( $user->instances as $_instance )
			{
				if ( 1 == $_instance->trial_instance_ind )
				{
					return $_instance->getRestAttributes();
				}

				unset( $_instance );
			}
		}

		return false;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function dspAvailable( $name )
	{
		//	See if there is one by this name already?
		if ( null !== ( $_instance = Instance::model()->findByAttributes( array( 'instance_name_text' => $name ) ) ) )
		{
			unset( $_instance );

			return false;
		}

		return true;
	}
}
