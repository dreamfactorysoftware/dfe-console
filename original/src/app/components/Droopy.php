<?php
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\Curl;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;

/**
 * Droopy.php
 *
 * @copyright Copyright (c) 2012-2013 DreamFactory Software, Inc.
 * @link      http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 *
 * @filesource
 */
class Droopy extends Curl
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const DEFAULT_REGISTRATION_ENDPOINT = 'https://www.dreamfactory.com/user.json';
	/**
	 * @var string
	 */
	const DEFAULT_TOKEN_ENDPOINT = 'https://www.dreamfactory.com/restws/session/token';
	/**
	 * @var string
	 */
	const DEFAULT_USER = 'restws_ops';
	/**
	 * @var string
	 */
	const DEFAULT_PASSWORD = 'restws_user';

	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * @var string
	 */
	protected static $_token = null;
	/**
	 * @var string
	 */
	protected static $_cookie = null;

	//**************************************************************************
	//* Methods
	//**************************************************************************

	/**
	 * @param string $user
	 * @param string $pass
	 * @param string $endpoint
	 *
	 * @return string
	 */
	public static function getToken( $user = self::DEFAULT_USER, $pass = self::DEFAULT_PASSWORD, $endpoint = self::DEFAULT_TOKEN_ENDPOINT )
	{
		if ( empty( static::$_token ) )
		{
			$_result = static::post(
							 $endpoint,
							 array(),
							 array(
								 CURLOPT_USERPWD        => $user . ':' . $pass,
								 CURLOPT_RETURNTRANSFER => true,
							 )
			);

			if ( HttpResponse::Ok == static::getLastHttpCode() )
			{
				//	Get the session cookie
				static::$_cookie = Option::get( static::$_lastResponseHeaders, 'Set-Cookie' );

				//	Return the token
				return static::$_token = $_result;
			}

			return false;
		}

		return true;
	}

	/**
	 * @param array $payload
	 * @param bool  $skipped
	 *
	 * @return bool
	 */
	public static function registerUser( array $payload, $skipped = false )
	{
		$_options = array(
			CURLOPT_USERPWD    => static::DEFAULT_USER . ':' . static::DEFAULT_PASSWORD,
			CURLOPT_HTTPHEADER => array(
				'Content-type: application/json',
			),
		);

		$payload['status'] = 1;
		$payload['field_welcome_skipped'] = $skipped ? 1 : 0;
		$payload['field_welcome_registration'] = $skipped ? 0 : 1;
		$payload['field_mobile_lead'] = 0;

		if ( false !== ( $_result = static::post( static::DEFAULT_REGISTRATION_ENDPOINT, json_encode( $payload ), $_options ) ) )
		{
			if ( null !== ( $_id = Option::get( $_result, 'id' ) ) )
			{
				Log::info( '  * Registration success. User ID = ' . $_id . ' @ ' . Option::get( $_result, 'uri' ) . ' -- ' . ( $skipped ? 'Skipped' : 'Registered' ) );
			}

			return $_result;
		}

		return false;
	}
}
