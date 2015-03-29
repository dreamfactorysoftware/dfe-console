<?php
/**
 * DeveloperUserIdentity.php
 */
use \Kisma\Core\Utility as Utility;

/**
 * DeveloperUserIdentity
 * Provides a password-based login against the database that is passthru to the model.
 */
class DeveloperUserIdentity extends \DreamFactory\Yii\Components\DreamUserIdentity
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var int
	 */
	const Authenticated = 0;
	/**
	 * @var int
	 */
	const InvalidCredentials = 1;

	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * @var string
	 */
	protected $_userClass = 'User';

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Authenticates a user.
	 *
	 * @return boolean
	 */
	public function authenticate()
	{
		//	Hand off to the model to authenticate...
		$_userId = call_user_func(
			array( $this->_userClass, 'authenticate' ),
			$this->username,
			$this->password
		);

		if ( empty( $_userId ) )
		{
			$this->errorCode = static::InvalidCredentials;

			return false;
		}

		$this->errorCode = static::Authenticated;
		$this->_userId = $_userId;

		return true;
	}

	/**
	 * @param string $userClass
	 *
	 * @return DeveloperUserIdentity
	 */
	public function setUserClass( $userClass )
	{
		$this->_userClass = $userClass;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUserClass()
	{
		return $this->_userClass;
	}

}