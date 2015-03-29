<?php
namespace Cerberus\Commands;

use Cerberus\Enums\InstanceStates;
use Cerberus\Services\Hosting\Instance\Snapshot;
use Cerberus\Services\Provisioning\DreamFactory\HostedInstance;
use Cerberus\Yii\Models\Auth\User;
use Cerberus\Yii\Models\Deploy\Instance;
use Cerberus\Yii\Models\Drupal\Users;
use DreamFactory\Enums\GelfLevels;
use DreamFactory\Enums\MailTemplates;
use DreamFactory\Interfaces\Graylog;
use DreamFactory\Services\Graylog\GelfLogger;
use DreamFactory\Services\Provisioning\Jobs\Amazon\EC2;
use DreamFactory\Yii\Commands\BaseQueueServicingCommand;
use DreamFactory\Yii\Exceptions\RestException;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\Curl;
use Kisma\Core\Utility\Inflector;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;

/**
 * ProcessQueueCommand
 * Job dequeue/process thread
 */
class ProcessQueueCommand extends BaseQueueServicingCommand
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var string
     */
    const LAUNCH_SUCCESS_TEXT = 'Your DSP has been created. You may now reach it by going to <a href="http://%%DSP_NAME%%.cloud.dreamfactory.com">http://%%DSP_NAME%%.cloud.dreamfactory.com</a> from any browser.';
    /**
     * @var string
     */
    const LAUNCH_FAILURE_TEXT = 'Your DSP launch did not succeed. Our engineers will examine the issue and notify you when it has been resolved. Hang tight, we\'ve got it.';
    /**
     * @var string
     */
    const DEPROVISION_SUCCESS_TEXT = 'Your DSP destruction was successful. If you would like to launch another one, just visit your dashboard.';
    /**
     * @var string
     */
    const DEPROVISION_FAILURE_TEXT = 'Your DSP destruction request did not succeed. Our engineers will examine the issue and notify you when/if it has been resolved. Hang tight, we\'ve got it.';

    //*************************************************************************
    //	Members
    //*************************************************************************

    /**
     * @var array The allowed Drupal fields for the registration queue
     */
    protected static $_drupalFields = array(
        'field_title',
        'field_address_1',
        'field_address_2',
        'field_city',
        'field_company_name',
        'field_country',
        'field_first_name',
        'field_last_name',
        'field_phone_number',
        'field_state_province',
        'field_zip_postal_code',
        'field_mobile_lead',
        'field_installation_type',
        'field_welcome_registration',
        'field_welcome_skipped',
    );

    //*************************************************************************
    //* Methods
    //*************************************************************************

    public function init()
    {
        $this->defaultAction = 'process';

        parent::init();
    }

    /**
     * Pro/Deprovisioning sub-handler
     *
     * @param array $job
     * @param bool  $deprovision
     *
     * @throws \CDbException
     * @return string
     */
    protected function _processInstanceRequest( $job, $deprovision = false )
    {
        $_instanceId = $_returnValue = null;
        $_label = ( $deprovision ? 'Deprovision' : 'Provision' );

        $this->logInfo( 'BEGIN > ' . $_label . ' Instance' );

        //	Find instance row that started this
        /** @var $_model Instance */
        $_model = Instance::model()->findByAttributes(
            array(
                'id'      => $job['payload']['id'],
                'user_id' => $job['payload']['user_id']
            )
        );

        if ( null === $_model )
        {
            $_message =
                'Not provisioned because of error: Instance "' .
                $job['payload']['instance_name_text'] .
                '" not registered with system.';

            $this->logError( '  * ' . $_message );

            if ( $deprovision )
            {
                $this->logNotice( '    * Marking work unit complete because of lack of registration.' );

                return array('details' => 'Instance row MIA and removed from system.');
            }

            return array('details' => $_message);
        }

        //	Get the additional data ready
        $_logInfo = array(
            'short_message' => $_label . ' request: ' . $job['payload']['instance_name_text'],
            'full_message'  => $_label . ' request: ' . $job['payload']['instance_name_text'],
            'level'         => GelfLevels::Info,
            'facility'      => Graylog::DefaultFacility . '/queue/provision',
            'source'        => 'cli',
            'payload'       => $job,
        );

        $_elapsed = null;
        $_output = array();

        if ( Instance::FABRIC_HOSTED == $_model->guest_location_nbr )
        {
            //	Update the current instance state
            $_model->updateState( $deprovision ? InstanceStates::DEPROVISIONING : InstanceStates::PROVISIONING );

            $_timestamp = microtime( true );

            $_returnValue = $this->_provisionHostedInstance( $_model, $deprovision );
            $_model->refresh();

            $_logInfo['elapsed'] = $_elapsed = ( microtime( true ) - $_timestamp );
        }
        else
        {
            $_command = Pii::getParam( 'script.' . strtolower( $_label ) ) . ' ';

            if ( $deprovision )
            {
                $_command .=
                    escapeshellarg( $job['payload']['instance_id_text'] ) .
                    ' ' .
                    escapeshellarg( $job['payload']['instance_name_text'] );
            }
            else
            {
                $_command .=
                    escapeshellarg( $job['payload']['instance_name_text'] ) .
                    ' ' .
                    escapeshellarg( $job['payload']['base_image_text'] );
            }

            $this->logDebug( '  * Calling ' . strtolower( $_label ) . 'ing script > ' . $_command );

            //	Update the current instance state
            $_model->updateState( $deprovision ? InstanceStates::DEPROVISIONING : InstanceStates::PROVISIONING );

            $_logFile =
                '/opt/dreamfactory/log/' . strtolower( $_label ) . '-' . $job['payload']['instance_name_text'] . '.log';

            $_timestamp = microtime( true );
            exec( $_command, $_output, $_returnValue );
            $_logInfo['elapsed'] = $_elapsed = ( microtime( true ) - $_timestamp );

            file_put_contents( $_logFile, implode( PHP_EOL, $_output ), FILE_APPEND );
        }

        //	Format and echo the results
        $this->logDebug(
            '  * Back from ' . strtolower( $_label ) . 'ing script > Return value = ' . print_r( $_returnValue, true )
        );

        if ( !empty( $_output ) )
        {
            $this->logDebug( PHP_EOL . '=====[ START ' . strtoupper( $_label ) . ' LOG ]=====' );
            $this->logDebug( implode( PHP_EOL, $_output ) . PHP_EOL );
            $this->logDebug( '=====[ END ' . strtoupper( $_label ) . ' LOG ]=====' . PHP_EOL );
        }

        if ( 0 != $_returnValue )
        {
            $_logInfo['success'] = false;
            $_logInfo['level'] = GelfLevels::Error;
            $this->logDebug( '  # Sending GELF log entry' );
            GelfLogger::logMessage( $_logInfo );

            $_model->updateState(
                $deprovision ? InstanceStates::DEPROVISIONING_ERROR : InstanceStates::PROVISIONING_ERROR
            );

            $this->_sendNotification(
                $_model->user,
                $deprovision ? 'DSP Shutdown Failure' : 'DSP Launch Failure',
                str_replace(
                    '%%DSP_NAME%%',
                    $_model->instance_name_text,
                    $deprovision ? static::DEPROVISION_FAILURE_TEXT : static::LAUNCH_FAILURE_TEXT
                )
            );

            return $_output;
        }

        if ( Instance::FABRIC_HOSTED == $_model->guest_location_nbr )
        {
            $_instanceId = $_model->instance_name_text;
        }
        else if ( $deprovision )
        {
            $_instanceId = $_model->instance_id_text;
        }
        else
        {
            if ( Instance::FABRIC_HOSTED == $_model->guest_location_nbr )
            {
                $_instanceId = $_model->instance_name_text;
            }
            else
            {
                foreach ( $_output as $_line )
                {
                    if ( 'instance id:' == strtolower( substr( $_line, 0, 12 ) ) )
                    {
                        $_instanceId = trim( str_ireplace( 'instance id: ', null, $_line ) );
                        break;
                    }
                }
            }
        }

        if ( !$deprovision && null === $_instanceId )
        {
            $_logInfo['success'] = false;
            $_logInfo['level'] = GelfLevels::Error;
            $this->logDebug( '  # Sending GELF log entry' );
            GelfLogger::logMessage( $_logInfo );

            $this->logError(
                '  * Appears that ' . strtolower( $_label ) . 'ing failed. Unable to locate new instance ID!'
            );

            $this->_sendNotification(
                $_model->user,
                $deprovision ? 'DSP Shutdown Failure' : 'DSP Launch Failure',
                $_label . ' Failure',
                str_replace(
                    '%%DSP_NAME%%',
                    $_model->instance_name_text,
                    $deprovision ? static::DEPROVISION_FAILURE_TEXT : static::LAUNCH_FAILURE_TEXT
                )
            );

            return $_output;
        }

        $this->logInfo( '  * ' . $_label . 'ed instance ID: ' . $_instanceId );

        /** @var $_node \stdClass */
        $_node = null;

        if ( Instance::FABRIC_HOSTED != $_model->guest_location_nbr )
        {
            //	Pull the details on the newly created instance...
            $_client = new EC2();
            $_service = $_client->getService();

            if ( null === ( $_instance = $_service->describe_instances( array('InstanceId' => $_instanceId) ) ) )
            {
                return array('log' => $_output);
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $_body = $_instance->body->to_stdclass();

            if ( !$deprovision )
            {
                $_node = $_body->reservationSet->item->instancesSet->item;
                $_model->start_date = $_node->launchTime;
            }

            $_model->availability_zone_text = $deprovision ? null : $_node->placement->availabilityZone;
            $_model->region_text = $deprovision ? null : substr( $_node->placement->availabilityZone, 0, -1 );
            $_model->public_host_text = $deprovision ? null : $_node->dnsName;
            $_model->public_ip_text = $deprovision ? null : $_node->ipAddress;
            $_model->private_host_text = $deprovision ? null : $_node->privateDnsName;
            $_model->private_ip_text = $deprovision ? null : $_node->privateIpAddress;
        }
        else
        {
            $_model->start_date = date( 'c' );
        }

        $_model->provision_ind = $deprovision ? 0 : 1;
        $_model->deprovision_ind = $deprovision ? 1 : 0;

        if ( !$deprovision )
        {
            $_model->terminate_date = $_model->end_date = null;
        }
        else
        {
            $_model->terminate_date = $_model->end_date = date( 'c' );
        }

        $_model->instance_id_text = $deprovision ? null : $_instanceId;
        $_model->state_nbr = $deprovision ? InstanceStates::DEPROVISIONED : InstanceStates::PROVISIONED;

        try
        {
            $_model->save();
            Log::debug( '  * Instance row updated' );

            if ( $deprovision )
            {
                $_model->delete();
                Log::debug( '  * Instance row deleted' );
            }
        }
        catch ( \Exception $_ex )
        {
            Log::error( '  * Exception while saving settings: ' . $_ex->getMessage() );
        }

        $this->logInfo( 'Command Complete > ' . $_label );

        try
        {
            $this->_sendNotification(
                $_model->user,
                $deprovision ? 'DSP Shutdown Complete' : 'DSP Launch Complete',
                str_replace(
                    '%%DSP_NAME%%',
                    $_model->instance_name_text,
                    $deprovision ? static::DEPROVISION_SUCCESS_TEXT : static::LAUNCH_SUCCESS_TEXT
                ),
                $deprovision ? MailTemplates::DeprovisioningComplete : MailTemplates::ProvisioningComplete
            );
        }
        catch ( \Exception $_ex )
        {
            $this->logError( 'Exception sending confirmation: ' . $_ex->getMessage() );
            //	Non-fatal
        }

        $_logInfo['success'] = true;
        $_logInfo['instance'] = $_node;

        $this->logDebug( '  # Sending GELF log entry' );
        GelfLogger::logMessage( $_logInfo );

        $this->logInfo( 'COMPLETE > ' . $_label . ' Instance' );

        return array('instance' => $_node, 'log' => $_output);
    }

    /**
     * Provisioning queue handler
     *
     * @param array $job
     *
     * @throws \CDbException
     * @return string
     */
    protected function _processProvision( $job )
    {
        $this->logInfo( 'BEGIN > Provision Request' );

        $_result = $this->_processInstanceRequest( $job, false );

        $this->logInfo( 'COMPLETE > Provision Request' );

        return $_result;
    }

    /**
     * Deprovisioning queue handler
     *
     * @param array $job
     *
     * @throws \CDbException
     * @return string
     */
    protected function _processDeprovision( $job )
    {
        $this->logInfo( 'BEGIN > Deprovision Request' );

        $_result = $this->_processInstanceRequest( $job, true );

        $this->logInfo( 'COMPLETE > Deprovision Request' );

        return $_result;
    }

    /**
     * Email queue handler
     *
     * @param array $job
     *
     * @return array
     */
    protected function _processEmail( $job )
    {
        $this->logInfo( 'BEGIN > Email Request' );

        $_mailer = new \DreamMailer( $this );
        $_sendCount = $_mailer->send( $job['payload'] );

        $this->logInfo( 'COMPLETE > Email Request > ' . $_sendCount . ' email(s) sent.' );

        return array(
            'send_count' => $_sendCount,
        );
    }

    /**
     * Registration queue handler
     *
     * @param array $job
     *
     * @throws \Cerberus\Exceptions\AlreadyRegisteredException
     * @throws \RestException
     * @return array
     */
    protected function _processRegister( $job )
    {
        $_id = Option::get( $job, '_id' );

        $this->logInfo( 'BEGIN > Register Request' );

        $_data = Option::get( $job, 'payload', array() );
        $_email = filter_var( Option::get( $_data, 'email' ), FILTER_VALIDATE_EMAIL );

        if ( empty( $_email ) || 'user@example.com' == $_email )
        {
            $this->logNotice( '  * Invalid email specified for job id #' . $_id . ': ' . $_email );

            return array(
                'success' => true,
                'details' => array(
                    'message' => 'User email is "user@example.com". Not processing.',
                ),
            );
        }

        /** @var User $_model */
        if ( null !== ( $_model = Users::model()->byEmailAddress( $_email )->find() ) )
        {
            //	found, update queue
            $this->logNotice( '  * User "' . $_email . '" already in Drupal.' );

            return array(
                'success' => true,
                'details' => array(
                    'message' => 'User "user@example.com" is already in Drupal. Not processing.',
                ),
            );
        }

        $_payload = array(
            'name' =>
                Inflector::neutralize( Option::get( $_data, 'field_installation_type', 'Welcome Support' ) ) .
                '_' .
                sha1( $_email . microtime( true ) ),
            'mail' => $_email,
        );

        foreach ( $_data as $_key => $_value )
        {
            if ( in_array( $_key, static::$_drupalFields ) )
            {
                $_payload[$_key] = $_value;
            }
        }

        /** @var \stdClass $_result */
        $_result = \Droopy::registerUser( $_payload, Option::get( $_data, 'field_registration_skipped', 0 ) );

        if ( false === $_result )
        {
            $this->logError( '  ! Network error while registering "' . $_email . '" for job id #' . $_id );

            return array(
                'success' => false,
                'error'   => array_merge( Curl::getError(), array('info' => Curl::getInfo()) ),
            );
        }

        if ( empty( $_result ) || !( $_result instanceof \stdClass ) )
        {
            $this->logError( '  ! Unexpected result while registering user "' . $_email . '" for job id #' . $_id );

            $_error = array(
                'code'    => Curl::getLastHttpCode(),
                'message' => 'Unexpected result from registration endpoint',
                'details' => $_result,
                'info'    => Curl::getInfo(),
            );

            $this->logError( '  * Details: ' . print_r( $_error, true ) );

            return array(
                'success' => false,
                'error'   => $_error,
            );
        }

        $this->logInfo( '  * User "' . $_email . '" registered in Drupal: ' . print_r( $_result, true ) );

        $_skipped = ( 1 == Option::get( $_data, 'field_registration_skipped', 0 ) ? 'No' : 'Yes' );

        $_subject = '[CERBERUS] New "Welcome" User Registration';

        $_body = <<<HTML
<p>A new admin user was created on a non-hosted DSP. This user has been created in Drupal:</p>
<p>
	<table>
		<tr><td>Name:</td><td>{$_payload['name']}</td></tr>
		<tr><td>Email:</td><td>{$_payload['mail']}</td></tr>
		<tr><td>Wants Support?</td><td><strong>{$_skipped}</strong></td></tr>
		<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td>Drupal ID:</td><td>{$_result->id}</td></tr>
		<tr><td>Drupal URI:</td><td>{$_result->uri}</td></tr>
	</table>
</p>
HTML;

        $_data = array(
            'subject'     => $_subject,
            'header_text' => 'New "Welcome" Registration',
            'body_text'   => $_body,
            'to'          => array('marketing@dreamfactory.com' => 'DreamFactory Marketing'),
            'bcc'         => array('ops+logging@dreamfactory.com' => 'DreamFactory Operations Logging'),
            'from'        => array('no.reply@dreamfactory.com' => 'DreamFactory Cerberus'),
            'type'        => \MailTemplates::SystemNotificationEmail,
        );

        try
        {
            $_id = $this->_queue->enqueue( $_data, 'email' );
            Log::info( '  * Notification queued [' . $_subject . ']: ' . $_id );
        }
        catch ( \Exception $_ex )
        {
            Log::error( '  ! Exception sending notification: ' . $_ex->getMessage() );
            //	Burp
        }

        $this->logInfo( 'COMPLETE > Register Request > ' . $_id );

        return array(
            'success' => true,
            'details' => $_result
        );
    }

    /**
     * @param Instance $instance
     * @param bool     $deprovision
     *
     * @return int
     */
    protected function _provisionHostedInstance( $instance, $deprovision = false )
    {
        $_service = new HostedInstance( $this );

        $this->logInfo( 'BEGIN > Provisioning hosted instance' );

        $_payload = array(
            'name'        => $instance->instance_name_text,
            'storage_key' => $instance->storage_id_text,
        );

        if ( $deprovision )
        {
            if ( false === ( $_result = $_service->deprovision( $_payload ) ) )
            {
                return 1;
            }

            $this->logInfo( '  * Deprovisioned: ' . $instance->instance_name_text );
        }
        else
        {
            if ( false === ( $_result = $_service->provision( $_payload ) ) )
            {
                return 1;
            }

            $this->logInfo( '  * Provisioned: ' . $instance->instance_name_text );
        }

        if ( $_result )
        {
            $this->logDebug( '  # Results: ' . print_r( $_result, true ) );
        }

        $this->logInfo( 'COMPLETE > Provisioning hosted instance' );

        return 0;
    }

    /**
     * Snapshot queue handler
     *
     * @param array $job
     *
     * @throws \DreamFactory\Common\Exceptions\RestException
     * @throws \Exception
     * @return string
     */
    protected function _processSnapshot( $job )
    {
        $this->logInfo( 'BEGIN > Snapshot Request' );

        //	Find instance row that started this
        /** @var Instance $_instance */
        $_instance = Instance::model()->findByAttributes(
            array(
                'id'      => $job['payload']['id'],
                'user_id' => $job['payload']['user_id']
            )
        );

        if ( null === $_instance )
        {
            $this->logError( '  ! Unable to locate instance ID "' . $_instance->id . '" for snapshot.' );

            return 0;
        }

        $_snapshotService = new Snapshot( $this );

        if ( false === ( $_snapshot = $_snapshotService->create( $_instance->instance_name_text ) ) )
        {
            throw new \DreamFactory\Common\Exceptions\RestException(
                HttpResponse::InternalServerError,
                'The snapshot service is currently not available. Please try again later.'
            );
        }

        $_link = $_snapshot->download_link;

        $this->logInfo( '  * Snapshot created: ' . $_snapshot->file_path );
        $this->logInfo( '  *    Download Link: ' . $_link );

        $_body = <<<HTML
<p>The requested snapshot of your instance "{$_instance->instance_name_text}" is now available for download.</p>
<p>To download the file, click on the link below. If the link is not clickable, copy and paste it into your browser.</p>
<p style="text-indent:25px;"><a href="{$_link}" target="_blank">{$_link}</a></p>
HTML;

        $this->_sendNotification(
            $_instance->user,
            'Snapshot Complete',
            $_body,
            MailTemplates::NotificationEmail
        );

        $this->logInfo( 'COMPLETE > Snapshot Request' );

        return $_snapshot;
    }

    /**
     * Snapshot queue handler
     *
     * @param array $job
     *
     * @throws RestException
     * @return string
     */
    protected function _processImport( $job )
    {
        $this->logInfo( 'BEGIN > Import Request' );

        //	Find instance row that started this
        /** @var $_instance Instance */
        $_instance = Instance::model()->findByAttributes(
            array(
                'id'      => $job['payload']['id'],
                'user_id' => $job['payload']['user_id']
            )
        );

        if ( null === $_instance )
        {
            $this->logError( '  ! Unable to locate instance ID "' . $job['payload']['id'] . '" for import.' );

            return 0;
        }

        $_snapshotService = new Snapshot( $this );

        if ( false ===
            ( $_snapshot =
                $_snapshotService->restore( $_instance->instance_name_text, $job['payload']['snapshot_id'] ) )
        )
        {
            throw new RestException(
                HttpResponse::InternalServerError,
                'The import service is currently not available. Please try again later.'
            );
        }

        $_body = <<<HTML
<p>The requested snapshot import to your instance "{$_instance->instance_name_text}" has completed.</p>
<p>Please check it out to make sure everything is in order.</p>
HTML;

        /** @noinspection PhpParamsInspection */
        $this->_sendNotification(
            $_instance->user,
            'Import Complete',
            $_body,
            MailTemplates::NotificationEmail
        );

        $this->logInfo( 'COMPLETE > Import Request' );

        return $_snapshot;
    }
}
