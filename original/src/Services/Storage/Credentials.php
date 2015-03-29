<?php
namespace Cerberus\Services\Storage;

use Cerberus\Yii\Models\Deploy\Vendor;
use DreamFactory\Exceptions\StorageServiceException;
use DreamFactory\Services\DreamService;
use Cerberus\Yii\Models\Auth\User;
use Cerberus\Yii\Models\Auth\UserCredentials;
use DreamFactory\Yii\Exceptions\RestException;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;

/**
 * @file
 * @copyright     Copyright 2013 DreamFactory Software, Inc. All rights reserved.
 * @link          http://dreamfactory.com DreamFactory Software, Inc.
 */
class Credentials extends DreamService
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const DEFAULT_CREDENTIALS_LABEL = 'default';

	//*************************************************************************
	//* Public Actions
	//*************************************************************************

	/**
	 * Delete
	 *
	 * @param int|string $userId
	 * @param int        $vendorId
	 * @param string     $label
	 *
	 * @throws \DreamFactory\Exceptions\StorageServiceException
	 * @internal param int|string $id
	 *
	 * @return bool
	 */
	public static function delete( $userId, $vendorId, $label )
	{
//		Log::debug( 'api: DELETE UserCredentials: userId=' . $userId . ' vendorId=' . $vendorId, ' label=' . $label );

		$_user = static::_validateUser( $userId );
		$_vendor = static::_validateRequest( $vendorId );

		$_criteria = array(
			'condition' => ':user_id = user_id and vendor_id = :vendor_id and label_text = :label_text',
			'params'    => array(
				':user_id'    => $_user->id,
				':vendor_id'  => $_vendor->id,
				':label_text' => $label,
			)
		);

		try
		{
			return UserCredentials::model()->delete( $_criteria );
		}
		catch ( \Exception $_ex )
		{
			throw new StorageServiceException( $_ex->getMessage() );
		}
	}

	/**
	 * @param int    $userId
	 * @param int    $vendorId
	 * @param string $label
	 * @param bool   $jsonReady
	 *
	 * @return array
	 */
	public static function find( $userId, $vendorId = null, $label = null, $jsonReady = true )
	{
//		Log::debug( 'api: GET UserCredentials: userId=' . $userId . ' vendorId=' . $vendorId . ' label=' . $label );

		$_user = static::_validateUser( $userId );

		$_criteria = array(
			'condition' => ':user_id = user_id',
			'params'    => array(
				':user_id' => $_user->id,
			)
		);

		if ( null !== $vendorId )
		{
			$_vendor = static::_validateRequest( $vendorId );

			$_criteria['condition'] .= ' and :vendor_id = vendor_id';
			$_criteria['params'][':vendor_id'] = $_vendor->id;
		}

		if ( !empty( $label ) )
		{
			$_criteria['condition'] .= ' and :label_text = label_text';
			$_criteria['params'][':label_text'] = $label;
		}

		$_credentials = UserCredentials::model()->findAll( $_criteria );

		if ( empty( $_credentials ) )
		{
			Log::debug( 'No rows found: ' . print_r( $_criteria, true ) );

			return array();
		}

		if ( !$jsonReady )
		{
			return $_credentials;
		}

		$_result = array();

		/** @var $_cred UserCredentials */
		foreach ( $_credentials as $_cred )
		{
			$_result[$_cred->label_text] = $_cred->getRestAttributes();
			unset( $_cred );
		}

		unset( $_credentials );

		return $_result;
	}

	/**
	 * @param       $userId
	 * @param array $payload
	 *
	 * @param bool  $jsonReady
	 *
	 * @throws \DreamFactory\Yii\Exceptions\RestException
	 * @throws \InvalidArgumentException
	 * @return bool
	 */
	public static function upsert( $userId, $payload = array(), $jsonReady = true )
	{
		$_vendorId = Option::get( $payload, 'vendorId' );
		$_keys = Option::get( $payload, 'keys' );
		$_label = Option::get( $payload, 'label', static::DEFAULT_CREDENTIALS_LABEL );

		if ( !$_vendorId || !$userId || !$_keys )
		{
			throw new \InvalidArgumentException( 'Missing vendor, user or keys.' );
		}

		$_user = static::_validateUser( $userId );
		$_vendor = static::_validateRequest( $_vendorId );

		$_credentials = UserCredentials::model()->find(
			'user_id = :user_id and vendor_id = :vendor_id and label_text = :label_text',
			array(
				 ':user_id'    => $userId,
				 ':vendor_id'  => $_vendor->id,
				 ':label_text' => $_label,
			)
		);

		if ( empty( $_credentials ) )
		{
			$_credentials = new UserCredentials();
			$_credentials->user_id = $userId;
			$_credentials->vendor_id = $_vendor->id;
			$_credentials->label_text = $_label;
		}

		//	Only update keys
		$_credentials->keys_text = $_keys;

		try
		{
			if ( $_credentials->save() )
			{
				$_credentials->refresh();

				if ( !$jsonReady )
				{
					return $_credentials;
				}

				return $_credentials->getRestAttributes();
			}
		}
		catch ( \Exception $_ex )
		{
			throw new RestException( HttpResponse::InternalServerError, $_ex->getMessage() );
		}
	}

	/**
	 * @param int|string $id
	 * @param array      $payload
	 *
	 * @throws \InvalidArgumentException
	 * @return \Instance
	 */
	protected static function _validateRequest( $id, $payload = null )
	{
		if ( empty( $id ) )
		{
			throw new \InvalidArgumentException( 'Invalid id.' );
		}

		/** @var $_vendor Vendor */
		$_vendor = Vendor::model()->find(
			'vendor_name_text = :id or id = :id',
			array(
				 ':id' => $id,
			)
		);

		if ( null === $_vendor )
		{
			throw new \InvalidArgumentException( 'Vendor ID "' . $id . '" Not found.' );
		}

		return $_vendor;
	}

	/**
	 * @param int|string $id
	 *
	 * @throws \InvalidArgumentException
	 * @return User
	 */
	protected static function _validateUser( $id )
	{
		if ( empty( $id ) )
		{
			throw new \InvalidArgumentException( 'Invalid id.' );
		}

		/** @var $_user \User */
		$_user = User::model()->findByPk( $id );

		if ( null === $_user )
		{
			throw new \InvalidArgumentException( 'Invalid id.' );
		}

		return $_user;
	}
}