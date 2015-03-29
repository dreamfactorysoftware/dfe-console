<?php
use Cerberus\Yii\Controllers\ResourceController;
use Cerberus\Yii\Models\Auth\User;
use Cerberus\Yii\Models\Auth\UserCredentials;
use DreamFactory\Yii\Exceptions\RestException;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Log;

/**
 * CredentialsController
 */
class CredentialsController extends ResourceController
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
	 * Initialize and set our resource type
	 */
	public function init()
	{
		$this->_resourceClass = 'Cerberus\\Yii\\Models\\Auth\\UserCredentials';

		parent::init();
	}

	/**
	 * Delete a resource
	 *
	 * @param int|string $userId
	 * @param int        $vendorId
	 * @param string     $label
	 *
	 * @throws \DreamFactory\Yii\Exceptions\RestException
	 * @internal param int|string $id
	 *
	 * @return bool
	 */
	public function delete( $userId, $vendorId, $label )
	{
//		Log::debug( 'api: DELETE UserCredentials: userId=' . $userId . ' vendorId=' . $vendorId, ' label=' . $label );

		$_user = $this->_validateUser( $userId );
		$_vendor = $this->_validateRequest( $vendorId );

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
			throw new RestException( HttpResponse::InternalServerError, $_ex->getMessage() );
		}
	}

	/**
	 * @param int    $userId
	 * @param int    $vendorId
	 * @param string $label
	 *
	 * @return array
	 */
	public function get( $userId, $vendorId = null, $label = null )
	{
//		Log::debug( 'api: GET UserCredentials: userId=' . $userId . ' vendorId=' . $vendorId . ' label=' . $label );

		$_user = $this->_validateUser( $userId );

		$_criteria = array(
			'condition' => ':user_id = user_id',
			'params'    => array(
				':user_id' => $_user->id,
			)
		);

		if ( null !== $vendorId )
		{
			$_vendor = $this->_validateRequest( $vendorId );

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

		$_result = array();

		/** @var $_cred UserCredentials */
		foreach ( $_credentials as $_cred )
		{
			$_result[$_cred->label_text] = $_cred->getRestAttributes();
		}

		return $_result;
	}

	/**
	 * @param array $payload
	 *
	 * @return bool
	 * @throws DreamFactory\Yii\Exceptions\RestException
	 */
	public function post( $payload = null )
	{
		$_vendorId = FilterInput::post( 'vendorId' );
		$_userId = FilterInput::post( 'userId' );
		$_keys = FilterInput::post( 'keys' );
		$_label = FilterInput::post( 'label', static::DEFAULT_CREDENTIALS_LABEL );

		if ( !$_vendorId || !$_userId || !$_keys )
		{
			throw new RestException( HttpResponse::BadRequest, 'Missing vendor, user or keys.' );
		}

		$_vendor = $this->_validateRequest( $_vendorId );

		$_credentials = UserCredentials::model()->find(
			'user_id = :user_id and vendor_id = :vendor_id and label_text = :label_text',
			array(
				':user_id'    => $_userId,
				':vendor_id'  => $_vendorId,
				':label_text' => $_label,
			)
		);

		if ( empty( $_credentials ) )
		{
			$_credentials = new UserCredentials();
			$_credentials->user_id = $_userId;
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
	 * @throws DreamFactory\Yii\Exceptions\RestException
	 * @return \Instance
	 */
	protected function _validateRequest( $id, $payload = null )
	{
		if ( empty( $id ) )
		{
			throw new RestException( HttpResponse::BadRequest );
		}

		/** @var $_vendor \Vendor */
		$_vendor = \Vendor::model()->find(
			'vendor_name_text = :id or id = :id',
			array(
				':id' => $id,
			)
		);

		if ( null === $_vendor )
		{
			throw new RestException( HttpResponse::NotFound, 'Vendor ID "' . $id . '" Not found.' );
		}

		return $_vendor;
	}

	/**
	 * @param int|string $id
	 *
	 * @throws DreamFactory\Yii\Exceptions\RestException
	 *
	 * @return User
	 */
	protected function _validateUser( $id )
	{
		if ( empty( $id ) )
		{
			throw new RestException( HttpResponse::BadRequest );
		}

		/** @var $_user \User */
		$_user = User::model()->findByPk( $id );

		if ( null === $_user )
		{
			throw new RestException( HttpResponse::NotFound );
		}

		return $_user;
	}

	/**
	 * @param int $userId
	 * @param int $vendorId
	 *
	 * @throws DreamFactory\Yii\Exceptions\RestException
	 *
	 * @return UserCredentials
	 */
	protected function _validateCredentials( $userId, $vendorId )
	{
		if ( empty( $userId ) || empty( $vendorId ) )
		{
			throw new RestException( HttpResponse::BadRequest );
		}

		/** @var $_credentials UserCredentials */
		$_credentials = UserCredentials::model()->find(
			'user_id = :user_id and vendor_id = :vendor_id',
			array(
				':user_id'   => $userId,
				':vendor_id' => $vendorId
			)
		);

		if ( null === $_credentials )
		{
			throw new RestException( HttpResponse::NotFound );
		}

		return $_credentials;
	}

}