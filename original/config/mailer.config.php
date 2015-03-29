<?php
/**
 * mailer.config.php
 * This file contains all mailer related configuration settings
 *
 * @link    http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author  Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */

use DreamFactory\Enums\MailTemplates;

return array(

	//*************************************************************************
	//* Mailer Settings
	//*************************************************************************

	'mailer.web_url'               => 'http://cerberus.fabric.dreamfactory.com/',
	'mailer.public_url'            => 'http://cerberus.fabric.dreamfactory.com/',
	'mailer.support_email_address' => 'support@dreamfactory.com',
	'mailer.confirmation_url'      => 'http://cerberus.fabric.dreamfactory.com/app/confirmation/',
	'mailer.smtp_service'          => 'localhost',
	//.........................................................................
	//. Services/Keys
	//.........................................................................

	//	Amazon SES keys
	'mailer.services'              => array(
		'ses'      => array(
			'server_name' => 'email-smtp.us-east-1.amazonaws.com',
			'server_port' => '465',
			'access_key'  => 'AKIAJDPTBX2Q5SOGRE7Q',
			'secret_key'  => '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',
		),
		//	SendGrid.com credentials
		'sendgrid' => array(
			'server_name' => 'smtp.sendgrid.net',
			'server_port' => 587,
			'access_key'  => 'dreamfactory',
			'secret_key'  => 'Dreamer123!',
		),
	),
	//.........................................................................
	//. Templates
	//.........................................................................

	'mailer.templates'             => [
		MailTemplates::WelcomeEmail            => array(
			'subject'  => 'Welcome to DreamFactory Developer Central!',
			'template' => 'welcome-confirmation.html',
		),
		MailTemplates::PasswordReset           => array(
			'subject'  => 'Recover your DreamFactory password',
			'template' => 'recover-password.html',
		),
		MailTemplates::PasswordChanged         => array(
			'subject'  => 'Your Password Has Been Changed',
			'template' => 'password-changed.html',
		),
		MailTemplates::NotificationEmail       => array(
			'subject'  => null,
			'template' => 'notification.html',
		),
		MailTemplates::SystemNotificationEmail => array(
			'subject'  => null,
			'template' => 'system-notification.html',
		),
		MailTemplates::ProvisioningComplete    => array(
			'subject'  => 'Your DSP is ready!',
			'template' => 'provisioning-complete.html',
		),
		MailTemplates::DeprovisioningComplete  => array(
			'subject'  => 'Your DSP was removed!',
			'template' => 'deprovisioning-complete.html',
		),
	],
);
