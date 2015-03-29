<?php
/**
 * DreamServerUserIdentity.php
 */
use Cerberus\Yii\Models\Auth\ServiceUser;
use DreamFactory\Yii\Components\DreamUserIdentity;
use \Kisma\Core\Utility as Utility;

/**
 * DreamServerUserIdentity
 * Provides a password-based login against the database.
 */
class DreamServerUserIdentity extends DreamUserIdentity
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
	//* Public Methods
	//*************************************************************************

	/**
	 * Authenticates a user.
	 *
	 * @return boolean
	 */
	public function authenticate()
	{
		if ( false === ( $_userId = ServiceUser::authenticate( $this->username, $this->password ) ) )
		{
			$this->errorCode = self::InvalidCredentials;

			return false;
		}

		$this->errorCode = static::Authenticated;
		$this->_userId = $_userId;

		return true;
	}
}
