<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class RaveProvisioner extends BaseResourceProvisioner
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Get the current status of a provisioning request
     *
     * @param Instance $instance
     *
     * @return array
     */
    public function status( Instance $instance )
    {
        /** @var Instance $_instance */
        if ( null === ( $_instance = Instance::find( $instance->id ) ) )
        {
            return ['success' => false, 'error' => ['code' => 404, 'message' => 'Instance not found.']];
        }

        return ['success' => true, 'status' => $_instance->state_nbr, 'status_text' => ProvisionStates::prettyNameOf( $_instance->state_nbr )];
    }

    /**
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     *
     * @return array
     */
    protected function _doProvision( ProvisioningRequest $request )
    {
        $_output = [];
        $_result = false;
        $_instance = $request->getInstance();

        //	Update the current instance state
        $_instance->updateState( ProvisionStates::PROVISIONING );

        try
        {
            //  Provision storage and fill in the request
            $this->_provisionStorage( $request );

            //  And the instance
            if ( false === ( $_result = $this->_provisionInstance( $request ) ) )
            {
                throw new \RuntimeException( 'Exception during launch.' );
            }

            Mail::send(
                'email.generic',
                [
                    'firstName'     => $_instance->user->first_name_text,
                    'headTitle'     => 'Launch Complete',
                    'contentHeader' => 'Your DSP has been launched',
                    'emailBody'     =>
                        '<p>Your instance <strong>' .
                        $_instance->instance_name_text .
                        '</strong> has been successfully created. You can reach it by going to <a href="//' .
                        $_instance->public_host_text .
                        '">' .
                        $_instance->public_host_text .
                        '</a> from any browser.</p>',
                ],
                function ( $message ) use ( $_instance )
                {
                    /** @var Message $message */
                    $message
                        ->to( $_instance->user->email_addr_text, $_instance->user->first_name_text . ' ' . $_instance->user->last_name_text )
                        ->subject( '[DFE] Your DSP is ready!' );
                }
            );
        }
        catch ( \Exception $_ex )
        {
            $_instance->updateState( ProvisionStates::PROVISIONING_ERROR );

            Mail::send(
                'email.generic',
                [
                    'firstName'     => $_instance->user->first_name_text,
                    'headTitle'     => 'Launch Failure',
                    'contentHeader' => 'Your DSP has failed to launch',
                    'emailBody'     =>
                        '<p>Your instance <strong>' .
                        $_instance->instance_name_text .
                        '</strong> was not successfully created. Our engineers will examine the issue and notify you when it has been resolved. Hang tight, we\'ve got it.</p>',
                ],
                function ( $message ) use ( $_instance )
                {
                    /** @var Message $message */
                    $message
                        ->to( $_instance->user->email_addr_text, $_instance->user->first_name_text . ' ' . $_instance->user->last_name_text )
                        ->subject( '[DFE] DSP Launch Failure' );
                }
            );

            return ['success' => false, 'instance' => false, 'log' => $_output, 'result' => $_result];
        }

        return ['success' => true, 'instance' => $_instance->toArray(), 'log' => $_output, 'result' => $_result];
    }

    /**
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     *
     * @return array
     */
    protected function _doDeprovision( ProvisioningRequest $request )
    {
        $_output = [];
        $_result = false;
        $_instance = $request->getInstance();

        //	Update the current instance state
        $_instance->updateState( ProvisionStates::DEPROVISIONING );

        try
        {
            //  And the instance
            if ( 0 != ( $_result = $this->_deprovisionInstance( $request ) ) )
            {
                throw new \RuntimeException( 'Exception during deprovisioning.' );
            }

            \Mail::send(
                'email.generic',
                [
                    'firstName'     => $_instance->user->first_name_text,
                    'headTitle'     => 'Shutdown Complete',
                    'contentHeader' => 'Your DSP has been shut down',
                    'emailBody'     =>
                        '<p>Your instance <strong>' .
                        $_instance->instance_name_text .
                        '</strong> has been successfully shut down. A snapshot may be available in the dashboard under <strong>Snapshots</strong>.</p>',
                ],
                function ( $message ) use ( $_instance )
                {
                    /** @var Message $message */
                    $message
                        ->to( $_instance->user->email_addr_text, $_instance->user->first_name_text . ' ' . $_instance->user->last_name_text )
                        ->subject( '[DFE] Your DSP was shut down' );
                }
            );
        }
        catch ( \Exception $_ex )
        {
            $_instance->updateState( ProvisionStates::DEPROVISIONING_ERROR );

            \Mail::send(
                'email.generic',
                [
                    'firstName'     => $_instance->user->first_name_text,
                    'headTitle'     => 'Shutdown Complete',
                    'contentHeader' => 'Your DSP has been shut down',
                    'emailBody'     =>
                        '<p>Your instance <strong>' .
                        $_instance->instance_name_text .
                        '</strong> shut down was not successful. Our engineers will examine the issue and notify you if/when the issue has been resolved. Mostly likely you will not have to do a thing. But we will check it out just to be safe.'
                ],
                function ( $message ) use ( $_instance )
                {
                    /** @var Message $message */
                    $message
                        ->to( $_instance->user->email_addr_text, $_instance->user->first_name_text . ' ' . $_instance->user->last_name_text )
                        ->bcc( 'ops@dreamfactory.com', 'DreamFactory Operations' )
                        ->subject( '[DFE] DSP Shutdown Failure' );
                }
            );

            return ['instance' => false, 'log' => $_output];
        }

        return ['instance' => $_instance->toArray(), 'log' => $_output];
    }

    /**
     * @param ProvisioningRequest $request
     *
     * @return Filesystem
     */
    protected function _provisionStorage( ProvisioningRequest $request )
    {
        $_config = config( 'filesystems.disks.hosted' );

        if ( empty( $_config ) )
        {
            throw new \RuntimeException( 'No hosted storage configuration found.' );
        }

        //  Use requested file system if one...
        if ( null === ( $_filesystem = $request->getStorage() ) )
        {
            /** @type Filesystem $_filesystem */
            $_filesystem = \Storage::disk( 'hosted' );
            $request->setStorage( $_filesystem );
        }

        $_storage = new DreamFactoryRaveStorage();
        $_storage->provision( $request );

        return $_filesystem;
    }

}