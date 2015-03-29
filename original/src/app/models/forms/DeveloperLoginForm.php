<?php
/**
 * DeveloperLoginForm.php
 */
/**
 * DeveloperLoginForm
 * Provides a standard simple login form
 */
class DeveloperLoginForm extends \DreamFactory\Yii\Models\Forms\DreamLoginForm
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 *
	 * @param string $attribute
	 * @param array  $params
	 *
	 * @return void
	 */
	public function authenticate( $attribute, $params )
	{
		$this->_identity = new DeveloperUserIdentity( $this->email_addr_text, $this->password_text );

		if ( !$this->_identity->setUserClass( 'User' )->authenticate() )
		{
			\Kisma\Core\Utility\Log::error( 'AUTH_FAIL', array( 'user_name' => $this->email_addr_text ) );
			$this->addError( 'password', 'Incorrect username or password.' );
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 *
	 * @param bool $remember If true, cookie will be set for 30 days.
	 *
	 * @return boolean whether login is successful
	 */
	public function login( $remember = false )
	{
		//	No sneaking in the back door with Sally...
		if ( null === $this->_identity && false === $this->authenticate( $this->email_addr_text, $this->password_text ) )
		{
			return false;
		}

		//	User has been authenticated and now we log them in...
		if ( DeveloperUserIdentity::Authenticated === $this->_identity->errorCode )
		{
			$_duration = ( false === $remember ? 0 : \Kisma\Core\Enums\DateTime::SecondsPerDay ) * 30;
			\DreamFactory\Yii\Utility\Pii::user()->login( $this->_identity, $_duration );

			//	Mark the login...
			User::timestamp( $this->_identity->getUserId() );

			return true;
		}

		return false;
	}
}
