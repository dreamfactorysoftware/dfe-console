<?php
/**
 * MailTemplates.php
 */
class MailTemplates extends \Kisma\Core\Enums\SeedEnum
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var int
	 */
	const SystemNotificationEmail = -1;
	/**
	 * @var int
	 */
	const WelcomeEmail = 0;
	/**
	 * @var int
	 */
	const PasswordReset = 1;
	/**
	 * @var int
	 */
	const NotificationEmail = 2;
	/**
	 * @var int
	 */
	const ResendConfirmationEmail = 3;
	/**
	 * @var int
	 */
	const StatusEmail = 4;
	/**
	 * @var int
	 */
	const PasswordChanged = 5;
	/**
	 * @var int
	 */
	const ProvisioningComplete = 6;
	/**
	 * @var int
	 */
	const DeprovisioningComplete = 6;
	/**
	 * @var int
	 */
	const GenericEmail = 100;
}
