<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Common\Traits\LockingService;
use DreamFactory\Enterprise\Common\Traits\TemplateEmailQueueing;
use DreamFactory\Library\Fabric\Auditing\Enums\AuditLevels;
use DreamFactory\Library\Fabric\Auditing\Facades\Audit;
use Illuminate\Mail\Message;

/**
 * A base class for all provisioners
 *
 * This class provides a foundation upon which to build other PaaS provisioners for the DFE ecosystem. Merely extend the class and add the
 * _doProvision and _doDeprovision methods.
 */
abstract class BaseResourceProvisioner extends BaseService implements ResourceProvisioner
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
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function provision( $request, $options = [] )
    {
        $_elapsed = null;
        $_timestamp = microtime( true );
        $_result = $this->_doProvision( $request );
        $_elapsed = microtime( true ) - $_timestamp;
        $_instance = $request->getInstance();

        $this->_logProvision( ['instance' => $_instance, 'elapsed' => $_elapsed, 'result' => $_result, 'deprovision' => false, 'provision' => true] );

        //  Send notification
        $_data = [
            'firstName'     => $_instance->user->first_name_text,
            'headTitle'     => $_result ? 'Launch Complete' : 'Launch Failure',
            'contentHeader' => $_result ? 'Your DSP has been launched' : 'Your DSP was not launched',
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

        $_subject = $_result ? '[DFE] Your DSP is ready!' : '[DFE] DSP Launch Failure';

        \Mail::send(
            'email.generic',
            $_data,
            function ( Message $message ) use ( $_instance, $_subject )
            {
                $message
                    ->to( $_instance->user->email_addr_text, $_instance->user->first_name_text . ' ' . $_instance->user->last_name_text )
                    ->subject( $_subject );
            }
        );

        return $_result;
    }

    /** @inheritdoc */
    public function deprovision( $request, $options = [] )
    {
        $_elapsed = null;
        $_timestamp = microtime( true );
        $_result = $this->_doDeprovision( $request );
        $_elapsed = microtime( true ) - $_timestamp;
        $_instance = $request->getInstance();

        $this->_logProvision( ['instance' => $_instance, 'elapsed' => $_elapsed, 'result' => $_result, 'deprovision' => true, 'provision' => false] );

        //  Send notification
        $_data = [
            'firstName'     => $_instance->user->first_name_text,
            'headTitle'     => $_result ? 'Termination Complete' : 'Termination Failure',
            'contentHeader' => $_result ? 'Your DSP has been terminated' : 'Your DSP was not terminated',
            'emailBody'     => $_result
                ?
                '<p>Your instance <strong>' . $_instance->instance_name_text . '</strong> ' .
                'has been terminated.  A snapshot may be available in the dashboard under <strong>Snapshots</strong>.</p>'
                :
                '<p>Your instance <strong>' .
                $_instance->instance_name_text .
                '</strong> termination was not successful. Our engineers will examine the issue and, if necessary, notify you if/when the issue has been resolved. Mostly likely you will not have to do a thing. But we will check it out just to be safe.</p>',
        ];

        $_subject = $_result ? '[DFE] Your DSP is ready!' : '[DFE] DSP Launch Failure';

        \Mail::send(
            'email.generic',
            $_data,
            function ( Message $message ) use ( $_instance, $_subject )
            {
                $message
                    ->to( $_instance->user->email_addr_text, $_instance->user->first_name_text . ' ' . $_instance->user->last_name_text )
                    ->subject( $_subject );
            }
        );

        return $_result;
    }

    /**
     * @param array  $data
     * @param int    $level
     * @param string $facility
     */
    protected function _logProvision( $data = [], $level = AuditLevels::INFO, $facility = self::DEFAULT_FACILITY )
    {
        //  Put instance ID into the correct place
        $data['dfe'] = ['instance_id' => $data['instance']->instance_id_text];

        Audit::log( $data, $level, $facility, app( 'request' ) );
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