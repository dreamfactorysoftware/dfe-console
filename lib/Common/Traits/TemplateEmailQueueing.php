<?php
namespace DreamFactory\Enterprise\Common\Traits;

use DreamFactory\Enterprise\Common\Enums\MailTemplates;
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Utility\IfSet;

/**
 * A trait for queuing template emails from the DFE console
 */
trait TemplateEmailQueueing
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_defaultPrefix = '[DFE] ';
    /**
     * @type string
     */
    protected $_defaultBcc = ['ops@dreamfactory.com', 'support@dreamfactory.com'];
    /**
     * @type string
     */
    protected $_defaultFrom = ['no.reply@dreamfactory.com' => 'DreamFactory Enterprise(tm) Console'];

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @param int    $template The template to use
     * @param array  $envelope Array of envelope/addressing information used to fill out the template and email.
     *                         Envelope information is as follows:
     *
     *                         subject              the subject of the email
     *                         first-name           recipient first name
     *                         last-name            recipient last name
     *                         email                recipient email address
     *                         full-name            recipient full name (optional)
     *                         bcc                  an array of email addresses to BCC (optional)
     *                         cc                   an array of email addresses to CC (optional)
     *                         from                 an array [:email => :full-name] of the sender
     *
     * @param string $body     The body of the email
     *
     * @return bool
     * @throws \Exception
     */
    public function queueTemplateEmail( $template = MailTemplates::NOTIFICATION, $envelope, $body )
    {
        $_first = IfSet::get( $envelope, 'first-name' );
        $_last = IfSet::get( $envelope, 'last-name' );
        $_email = IfSet::get( $envelope, 'email' );
        $_full = IfSet::get( $envelope, 'full-name', $_first . ' ' . $_last );
        $_subject = IfSet::get( $envelope, 'subject' );
        $_from = IfSet::get( $envelope, 'from', $this->_defaultFrom );
        $_bccList = array_merge( $this->_defaultBcc, IfSet::get( $envelope, 'bcc', [] ) );
        $_cc = IfSet::get( $envelope, 'cc', [] );

        $_bcc = [];

        if ( !empty( $_bccList ) )
        {
            foreach ( $_bccList as $_item )
            {
                $_bcc[$_item] = $this->_defaultPrefix . $_subject;
            }
        }

        $_data = [
            'first_name_text' => $_first,
            'last_name_text'  => $_last,
            'email_text'      => $body,
            'subject'         => $_subject,
            'to'              => [$_email => $_full],
            'bcc'             => $_bcc,
            'from'            => $_from,
        ];

        if ( !empty( $_cc ) )
        {
            $_data['cc'] = $_cc;
        }

        try
        {
            //@todo queue email
            app( 'mail' )->send( $template, $_data );

            return true;
        }
        catch ( \Exception $_ex )
        {
            throw $_ex;
        }
    }

    /**
     * @param User  $user
     * @param array $extras Any extra envelope information
     *
     * @return array
     */
    protected function _createEnvelopeFromUser( User $user, $extras = [] )
    {
        return array_merge(
            [
                'first-name' => $user->first_name_text,
                'last-name'  => $user->last_name_text,
                'full-name'  => $user->first_name_text . ' ' . $user->last_name_text,
                'email'      => $user->email_addr_text,
            ],
            $extras
        );
    }
}