<?php
use DreamFactory\Enterprise\Common\Enums\MailTemplates;

/**
 * DFE core services configuration
 */
return [
    //******************************************************************************
    //* Mailgun
    //******************************************************************************
    'mailgun'       => [
        'domain' => env( 'MAILGUN_DOMAIN' ),
        'secret' => env( 'MAILGUN_SECRET_KEY' ),
    ],
    //******************************************************************************
    //* Mail template service
    //******************************************************************************
    'mail-template' => [
        'web-url'               => 'http://cerberus.fabric.dreamfactory.com/',
        'public-url'            => 'http://cerberus.fabric.dreamfactory.com/',
        'support-email-address' => 'support@dreamfactory.com',
        'confirmation-url'      => 'http://cerberus.fabric.dreamfactory.com/app/confirmation/',
        'smtp-service'          => 'localhost',
        //.........................................................................
        //. Templates
        //.........................................................................
        'templates'             => [
            MailTemplates::WELCOME              => [
                'subject'  => 'Welcome to DreamFactory Developer Central!',
                'template' => 'welcome-confirmation.html',
            ],
            MailTemplates::PASSWORD_RESET       => [
                'subject'  => 'Recover your DreamFactory password',
                'template' => 'recover-password.html',
            ],
            MailTemplates::PASSWORD_CHANGED     => [
                'subject'  => 'Your Password Has Been Changed',
                'template' => 'password-changed.html',
            ],
            MailTemplates::NOTIFICATION         => [
                'subject'  => null,
                'template' => 'notification.html',
            ],
            MailTemplates::SYSTEM_NOTIFICATION  => [
                'subject'  => null,
                'template' => 'system-notification.html',
            ],
            MailTemplates::PROVISION_COMPLETE   => [
                'subject'  => 'Your DSP is ready!',
                'template' => 'provisioning-complete.html',
            ],
            MailTemplates::DEPROVISION_COMPLETE => [
                'subject'  => 'Your DSP was removed!',
                'template' => 'deprovisioning-complete.html',
            ],
        ],
    ],
];
