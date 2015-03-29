<?php
/**
 * TestEmailCommand.php
 */
use Kisma\Core\Utility\Log;

/**
 * TestEmailCommand
 * Tests sending email
 */
class TestEmailCommand extends \DreamFactory\Yii\Commands\BaseQueueServicingCommand
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param string $to
	 * @param string $subject
	 * @param string $text
	 *
	 * @return void
	 */
	public function actionSend( $to, $subject, $text )
	{
		$_data = array(
			'subject'         => $subject,
			'first_name_text' => $to,
			'email_text'      => $text,
			'to'              => $to,
			'bcc'             => array( 'developer-central@dreamfactory.com' => '[DFDC] ' . $subject ),
			'from'            => array( 'developer-central@dreamfactory.com' => 'DreamFactory Developer Central' ),
			'type'            => MailTemplates::NotificationEmail,
		);

		try
		{
			$_id = $this->_queue->enqueue( $_data, 'email' );
			Log::info( 'Email queued: ' . $_id );
		}
		catch ( Exception $_ex )
		{
			Log::error( 'Exception sending notification: ' . $_ex->getMessage() );
		}

		return true;
	}

}