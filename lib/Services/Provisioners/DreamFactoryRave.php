<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Storage\DreamFactory\StorageProvisioner;
use DreamFactory\Enterprise\Services\Utility\RemoteInstance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DreamFactoryRave extends BaseProvisioner
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
     * @param \DreamFactory\Enterprise\Services\Utility\RemoteInstance           $instance
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     *
     * @return array
     */
    protected function _doProvision( $instance, ProvisioningRequest $request )
    {
        $_output = [];
        $_launchResult = false;

        //	Update the current instance state
        $instance->updateState( ProvisionStates::PROVISIONING );

        try
        {
            //  Provision storage and fill in the request
            $this->_provisionStorage( $request );

            //  And the instance
            if ( false === ( $_launchResult = $instance->up( $request ) ) )
            {
                throw new \RuntimeException( 'Exception during launch.' );
            }

            Mail::send(
                'email.generic',
                [
                    'firstName'     => $instance->user->first_name_text,
                    'headTitle'     => 'Launch Complete',
                    'contentHeader' => 'Your DSP has been launched',
                    'emailBody'     =>
                        '<p>Your instance <strong>' .
                        $instance->instance_name_text .
                        '</strong> has been successfully created. You can reach it by going to <a href="//' .
                        $instance->public_host_text .
                        '">' .
                        $instance->public_host_text .
                        '</a> from any browser.</p>',
                ],
                function ( $message ) use ( $instance )
                {
                    $message->to( $instance->user->email_addr_text, $instance->user->first_name_text . ' ' . $instance->user->last_name_text )
                        ->subject( '[DFE] Your DSP is ready!' );
                }
            );
        }
        catch ( \Exception $_ex )
        {
            $instance->updateState( ProvisionStates::PROVISIONING_ERROR );

            Mail::send(
                'email.generic',
                [
                    'firstName'     => $instance->user->first_name_text,
                    'headTitle'     => 'Launch Failure',
                    'contentHeader' => 'Your DSP has failed to launch',
                    'emailBody'     =>
                        '<p>Your instance <strong>' .
                        $instance->instance_name_text .
                        '</strong> was not successfully created. Our engineers will examine the issue and notify you when it has been resolved. Hang tight, we\'ve got it.</p>',
                ],
                function ( $message ) use ( $instance )
                {
                    $message->to( $instance->user->email_addr_text, $instance->user->first_name_text . ' ' . $instance->user->last_name_text )
                        ->subject( '[DFE] DSP Launch Failure' );
                }
            );

            return ['success' => false, 'instance' => false, 'log' => $_output, 'result' => $_launchResult];
        }

        return ['success' => true, 'instance' => $instance->toArray(), 'log' => $_output, 'result' => $_launchResult];
    }

    /**
     * @param RemoteInstance                                                     $instance
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     *
     * @return mixed|void
     */
    protected function _doDeprovision( $instance, ProvisioningRequest $request )
    {
        $_output = [];

        //	Update the current instance state
        $instance->updateState( ProvisionStates::DEPROVISIONING );

        try
        {
            //  And the instance
            if ( 0 != ( $_returnValue = $instance->down( $request ) ) )
            {
                throw new \RuntimeException( 'Exception during deprovisioning.' );
            }

            Mail::send(
                'email.generic',
                [
                    'firstName'     => $instance->user->first_name_text,
                    'headTitle'     => 'Shutdown Complete',
                    'contentHeader' => 'Your DSP has been shut down',
                    'emailBody'     =>
                        '<p>Your instance <strong>' .
                        $instance->instance_name_text .
                        '</strong> has been successfully shut down. A snapshot may be available in the dashboard under <strong>Snapshots</strong>.</p>',
                ],
                function ( $message ) use ( $instance )
                {
                    $message->to( $instance->user->email_addr_text, $instance->user->first_name_text . ' ' . $instance->user->last_name_text )
                        ->subject( '[DFE] Your DSP was shut down' );
                }
            );
        }
        catch ( \Exception $_ex )
        {
            $instance->updateState( ProvisionStates::DEPROVISIONING_ERROR );

            Mail::send(
                'email.generic',
                [
                    'firstName'     => $instance->user->first_name_text,
                    'headTitle'     => 'Shutdown Complete',
                    'contentHeader' => 'Your DSP has been shut down',
                    'emailBody'     =>
                        '<p>Your instance <strong>' .
                        $instance->instance_name_text .
                        '</strong> shut down was not successful. Our engineers will examine the issue and notify you if/when the issue has been resolved. Mostly likely you will not have to do a thing. But we will check it out just to be safe.'
                ],
                function ( $message ) use ( $instance )
                {
                    /** @var Message $message */
                    $message
                        ->to( $instance->user->email_addr_text, $instance->user->first_name_text . ' ' . $instance->user->last_name_text )
                        ->bcc( 'ops@dreamfactory.com', 'DreamFactory Operations' )
                        ->subject( '[DFE] DSP Shutdown Failure' );
                }
            );

            return ['instance' => false, 'log' => $_output];
        }

        return ['instance' => $instance->toArray(), 'log' => $_output];
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
            $_filesystem = Storage::disk( 'hosted' );
            $request->setStorage( $_filesystem );
        }

        $_storage = new StorageProvisioner();
        $_storage->provision( $request );

        return $_filesystem;
    }

}