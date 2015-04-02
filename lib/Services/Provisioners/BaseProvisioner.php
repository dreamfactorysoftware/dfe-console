<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Common\Traits\LockingService;
use DreamFactory\Enterprise\Common\Traits\TemplateEmailQueueing;
use DreamFactory\Enterprise\Services\Auditing\Audit;
use DreamFactory\Enterprise\Services\Auditing\Enums\AuditLevels;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Mail\Message;

/**
 * A base class for all provisioners
 *
 * This class provides a foundation upon which to build other PaaS provisioners for the DFE ecosystem. Merely extend the class and add the
 * _doProvision and _doDeprovision methods.
 */
abstract class BaseProvisioner extends BaseService implements ResourceProvisioner
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string This is the "facility" passed along to the auditing system for reporting
     */
    const DEFAULT_FACILITY = 'dfe-provision';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation, LockingService, TemplateEmailQueueing;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string A prefix for notification subjects
     */
    protected $_subjectPrefix;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function boot()
    {
        parent::boot();

        if ( empty( $this->_subjectPrefix ) )
        {
            $this->_subjectPrefix = config( 'dfe.email-subject-prefix', '[DFE]' );
        }
    }

    /** @inheritdoc */
    public function provision( $request, $options = [] )
    {
        $_timestamp = microtime( true );
        $_result = $this->_doProvision( $request );
        $_elapsed = microtime( true ) - $_timestamp;

        if ( is_array( $_result ) )
        {
            $_result['elapsed'] = $_elapsed;
        }

        $this->_logProvision( ['elapsed' => $_elapsed, 'result' => $_result] );

        //  Send notification
        $_instance = $request->getInstance();

        $_data = [
            'firstName'     => $_instance->user->first_name_text,
            'headTitle'     => $_result ? 'Launch Complete' : 'Launch Failure',
            'contentHeader' => $_result ? 'Your instance has been launched' : 'Your instance was not launched',
            'emailBody'     =>
                $_result
                    ?
                    '<p>Your instance <strong>' . $_instance->instance_name_text . '</strong> ' .
                    'has been created. You can reach it by going to <a href="//' . $_instance->public_host_text . '">' .
                    $_instance->public_host_text . '</a> from any browser.</p>'
                    :
                    '<p>Your instance <strong>' . $_instance->instance_name_text . '</strong> ' .
                    'was not created. Our engineers will examine the issue and notify you when it has been resolved. Hang tight, we\'ve got it.</p>',
        ];

        $_subject = $_result['success'] ? 'Instance launch successful' : 'Instance launch failure';

        $this->_notify( $_instance, $_subject, $_data );

        return $_result;
    }

    /** @inheritdoc */
    public function deprovision( $request, $options = [] )
    {
        $_timestamp = microtime( true );
        $_result = $this->_doDeprovision( $request );
        $_elapsed = microtime( true ) - $_timestamp;
        $_instance = $request->getInstance();

        if ( is_array( $_result ) )
        {
            $_result['elapsed'] = $_elapsed;
        }

        $this->_logProvision( ['elapsed' => $_elapsed, 'result' => $_result] );

        //  Send notification
        $_data = [
            'firstName'     => $_instance->user->first_name_text,
            'headTitle'     => $_result ? 'Shutdown Complete' : 'Shutdown Failure',
            'contentHeader' => $_result ? 'Your instance has retired' : 'Your instance was not retired',
            'emailBody'     => $_result
                ?
                '<p>Your instance <strong>' . $_instance->instance_name_text . '</strong> ' .
                'has been retired.  A snapshot may be available in the dashboard under <strong>Snapshots</strong>.</p>'
                :
                '<p>Your instance <strong>' .
                $_instance->instance_name_text .
                '</strong> shutdown was not successful. Our engineers will examine the issue and, if necessary, notify you if/when the issue has been resolved. Mostly likely you will not have to do a thing. But we will check it out just to be safe.</p>',
        ];

        $_subject = $_result['success'] ? 'Instance shutdown successful' : 'Instance shutdown failure';

        $this->_notify( $_instance, $_subject, $_data );

        return $_result;
    }

    /**
     * @param array $data
     * @param int   $level
     * @param bool  $deprovisioning
     *
     * @return bool
     */
    protected function _logProvision( $data = [], $level = AuditLevels::INFO, $deprovisioning = false )
    {
        //  Put instance ID into the correct place
        isset( $data['instance'] ) && $data['dfe'] = ['instance_id' => $data['instance']->instance_id_text];

        return Audit::log( $data, $level, app( 'request' ), ( ( $deprovisioning ? 'de' : null ) . 'provision' ) );
    }

    /**
     * @param Instance $instance
     * @param string   $subject
     * @param array    $data
     *
     * @return int The number of recipients mailed
     */
    protected function _notify( $instance, $subject, array $data )
    {
        if ( !empty( $this->_subjectPrefix ) )
        {
            $subject = $this->_subjectPrefix . ' ' . trim( str_replace( $this->_subjectPrefix, null, $subject ) );
        }

        $_result =
            \Mail::send(
                'emails.generic',
                $data,
                function ( $message ) use ( $instance, $subject )
                {
                    /** @var Message $message */
                    $message
                        ->to( $instance->user->email_addr_text, $instance->user->first_name_text . ' ' . $instance->user->last_name_text )
                        ->subject( $subject );
                }
            );

        $this->debug( '    * provisioner: notification sent to ' . $instance->user->email_addr_text );

        return $_result;
    }

    /**
     * @param ProvisioningRequest|mixed $request
     *
     * @return mixed
     */
    abstract protected function _doProvision( $request );

    /**
     * @param ProvisioningRequest|mixed $request
     *
     * @return mixed
     */
    abstract protected function _doDeprovision( $request );

}