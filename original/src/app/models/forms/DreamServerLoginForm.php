<?php
/**
 * DreamServerLoginForm.php
 */
/**
 * DreamServerLoginForm
 * Provides a standard simple login form
 */
class DreamServerLoginForm extends \DreamFactory\Yii\Models\Forms\DreamLoginForm
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
		$this->_identity = new DreamServerUserIdentity( $this->email_addr_text, $this->password_text );

		if ( !$this->_identity->authenticate() )
		{
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
		if ( null === $this->_identity )
		{
			$this->_identity = new DreamServerUserIdentity( $this->email_addr_text, $this->password_text );
			$this->_identity->authenticate();
		}

		if ( DreamServerUserIdentity::ERROR_NONE === $this->_identity->errorCode )
		{
			$_duration = 0;

			if ( false !== $remember )
			{
				$_duration = \Kisma\Core\Enums\DateTime::SecondsPerDay * 30;
			}

			\Yii::app()->user->login( $this->_identity, $_duration );

			return true;
		}

		return false;
	}
}
