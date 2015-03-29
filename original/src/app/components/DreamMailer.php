<?php
/**
 * DreamMailer.php
 *
 * @copyright Copyright (c) 2012 DreamFactory Software, Inc.
 * @link      http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 *
 * @filesource
 */
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Exceptions\ServiceException;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;

//	Load up SwiftMailer
$_vendorPath = Pii::getParam( 'app.vendor_path' );
require_once $_vendorPath . '/swiftmailer/swiftmailer/lib/classes/Swift.php';
Yii::registerAutoloader( array('Swift', 'autoload') );
require_once $_vendorPath . '/swiftmailer/swiftmailer/lib/swift_required.php';

/**
 * DreamMailer
 * Simple template emailer
 */
class DreamMailer extends \Kisma\Core\Services\DeliveryService
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var string The prefix for the config file
     */
    const SettingsPattern = '/^mailer\./i';
    /**
     * @var bool
     */
    const UseAmazonMailService = false;

    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var array
     */
    protected $_mailerSettings;

    //**************************************************************************
    //* Methods
    //**************************************************************************

    /**
     * @param Kisma\Core\Interfaces\ConsumerLike $consumer
     * @param array                              $settings
     */
    public function __construct( \Kisma\Core\Interfaces\ConsumerLike $consumer, $settings = array() )
    {
        parent::__construct( $consumer, $settings );

        //	Get our settings
        $this->_mailerSettings = array_merge(
            Pii::params( static::SettingsPattern, true ),
            $settings
        );
    }

    /**
     * Process the request (i.e. deliver payload)
     *
     * @param \Kisma\Core\Interfaces\RequestLike $payload
     *
     * @return \Kisma\Core\Interfaces\ResponseLike
     */
    public function deliver( $payload = null )
    {
        return $this->send( $payload );
    }

    /**
     * Sends an email
     *
     * @param array|\Kisma\Core\Interfaces\RequestLike|int $type
     * @param array|null                                   $data
     *
     * @throws ServiceException
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return bool
     */
    public function send( $type, $data = array() )
    {
        //	If we came in through a service call, make sure type is specified...
        if ( is_array( $type ) )
        {
            $data = $type;

            if ( null === ( $type = \Kisma\Core\Utility\Option::get( $data, 'type', null, true ) ) )
            {
                throw new \InvalidArgumentException( 'No template "type" specified in payload.' );
            }
        }

        if ( !\MailTemplates::contains( $type ) )
        {
            throw new \InvalidArgumentException( 'Invaliding mailing type "' . $type . '" specified.' );
        }

        $_templates = Option::get( $this->_mailerSettings, 'templates', array(), true );

        if ( !isset( $_templates[$type] ) )
        {
            throw new \InvalidArgumentException( 'There is no template configured for mailing type "' . $type . '".' );
        }

        if ( 'localhost' != $this->_mailerSettings['smtp_service'] )
        {
            if ( !isset( $this->_mailerSettings['access_key'] ) || !isset( $this->_mailerSettings['secret_key'] ) )
            {
                throw new \InvalidArgumentException( 'You must set both the "access_key" and "secret_key" in order to use this service.' );
            }
        }

        //	Use template subject first, then local
        if ( !isset( $data['subject'] ) )
        {
            $data['subject'] = \Kisma\Core\Utility\Option::get( $_templates[$type], 'subject' );
        }

        $_template = Pii::alias( 'application.config', null, '/templates/' . $_templates[$type]['template'] );

        if ( !file_exists( $_template ) )
        {
            throw new \InvalidArgumentException( 'Template "' . $_template . '" cannot be found.' );
        }

        if ( null === ( $_service = Option::get( $this->_mailerSettings, 'smtp_service' ) ) )
        {
            Log::warning( 'No service configured. Using "localhost".' );
        }

        //	Build the message
        $_message = $this->_createMessage( $_template, $data );

        try
        {
            switch ( $_service )
            {
                case 'localhost':
                    $_transport = new \Swift_MailTransport();
                    break;

                default:
                    if ( null === ( $_settings = Option::get( $this->_mailerSettings['services'], $_service ) ) )
                    {
                        throw new ServiceException( 'Service "' . $_service . '" has no configuration options.' );
                    }

                    //	Create the transport
                    /** @type \Swift_SmtpTransport $_transport */
                    /** @noinspection PhpUndefinedMethodInspection */
                    $_transport = \Swift_SmtpTransport::newInstance( $_settings['server_name'], $_settings['server_port'] )
                        ->setUsername( $_settings['access_key'] )
                        ->setPassword( $_settings['secret_key'] );
                    break;
            }

            //	And the mailer...
            $_mailer = new \Swift_Mailer( $_transport );

//			Log::debug( 'Sending Email' );
            $_recipients = $_mailer->send( $_message, $_bogus );

            if ( !empty( $_bogus ) )
            {
                Log::error( 'Failed recipients: ' . implode( ', ', $_bogus ) );
            }

            if ( empty( $_recipients ) )
            {
                Log::error( 'Sending email to "' . $_message->getTo() . '" failed.' );
            }
            else
            {
//				Log::debug( 'Sent. Recipient count: ' . $_recipients );
            }

            return $_recipients;
        }
        catch ( \Exception $_ex )
        {
            //	Something went awry
            Log::error( 'Mail delivery exception: ' . $_ex->getMessage() );
            throw $_ex;
        }
    }

    //**************************************************************************
    //* Private Methods
    //**************************************************************************

    /**
     * @param string $template
     * @param array  $data
     *
     * @throws InvalidArgumentException
     * @return \Swift_Mime_Message
     */
    protected function _createMessage( $template, &$data )
    {
        //	Pull out all the message data
        $_to = Option::get( $data, 'to', null, true );
        $_from = Option::get( $data, 'from', null, true );
        $_replyTo = Option::get( $data, 'reply_to', null, true );
        $_cc = Option::get( $data, 'cc', null, true );
        $_bcc = Option::get( $data, 'bcc', null, true );
        $_subject = Option::get( $data, 'subject', null, true );

        //	Get body template...
        if ( false === ( $_html = @file_get_contents( $template ) ) )
        {
            //	Something went awry
            throw new \InvalidArgumentException( 'Error reading contents of template "' . $template . '".' );
        }

        //	And the message...
        $_message = new \Swift_Message();

        if ( !empty( $_subject ) )
        {
            $_message->setSubject( $_subject );
        }

        if ( !empty( $_to ) )
        {
            $_message->setTo( $_to );
        }

        if ( !empty( $_from ) )
        {
            $_message->setFrom( $_from );
        }

        if ( !empty( $_cc ) )
        {
            $_message->setCc( $_cc );
        }

        if ( !empty( $_bcc ) )
        {
            $_message->setBcc( $_bcc );
        }

        if ( !empty( $_replyTo ) )
        {
            $_message->setReplyTo( $_replyTo );
        }

        //	process generic macros.
        $_message->setBody(
            $this->replaceMacros( $data, $_html ),
            'text/html'
        );

        return $_message;
    }

    /**
     * Given an array of macro data, the source string is augmented with said data.
     *
     * @param array  $replacements
     * @param string $source
     * @param string $prefix    Defaults to 'private_'
     * @param string $delimiter Defaults to '%%'
     *
     * @return string
     */
    protected function replaceMacros( $replacements, $source, $prefix = 'private_', $delimiter = '%%' )
    {
        $_data = array();

        //	Replace private macros...
        if ( false !== stripos( $source, $delimiter . $prefix ) )
        {
            foreach ( $replacements as $_key => $_value )
            {
                //	No passwords allowed
                if ( false !== stripos( $_key, 'password' ) )
                {
                    continue;
                }

                $_data[strtoupper( $delimiter . $prefix . $_key . $delimiter )] = $_value;
            }
        }

        //	With a sprinkle of settings...
        foreach ( $this->_mailerSettings as $_key => $_value )
        {
            if ( !is_scalar( $_value ) )
            {
                continue;
            }

            //	No passwords/keys/secret allowed
            if ( false !== stripos( $_key, 'password' ) || false !== stripos( $_key, 'secret_' ) || false !== stripos( $_key, '_key' ) )
            {
                continue;
            }

            //	No prefix on these...
            $_data[strtoupper( $delimiter . $_key . $delimiter )] = $_value;
        }

//		Log::debug( 'Creating message: ' . $source );
//		Log::debug( 'Message data: ' . print_r( $_data, true ) );

        //	Do all replacements
        $_result = str_ireplace(
            array_keys( $_data ),
            array_values( $_data ),
            $source
        );

        //	Return re-worked source
        return $_result;
    }
}
