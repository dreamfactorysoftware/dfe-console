<?php
namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Services\Contracts\InstanceControlContract;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Enums\MailTemplates;
use DreamFactory\Enterprise\Services\Hosting\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Hosting\Requests\ProvisioningRequest;
use DreamFactory\Enterprise\Services\InstanceService;
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Support\Facades\Config;

/**
 * The provisioning service is called when a job request is received.
 */
class ProvisioningService implements InstanceControlContract
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $instanceId
     * @param array  $options
     *
     * @return bool
     * @internal param string $instanceName
     */
    public function provision( $instanceId, $options = [] )
    {
        $_instance = $this->_validateInstance( $instanceId );

        $_data = [
            'owner-id'       => $_instance->user_id,
            'owner-key'      => $_instance->user->storage_key_text,
            'instance-name'  => $_instance->instance_name_text,
            'instance-key'   => $_instance->storage_key_text,
            'guest-location' => $_instance->guest_location_nbr,
            'ram-size'       => IfSet::get( $options, 'ram-size', 1 ),
            'disk-size'      => IfSet::get( $options, 'disk-size', 8 ),
            'storage'        => $this->_provisionStorage( $_instance ),
        ];

        $_request = new ProvisioningRequest( ProvisioningRequest::PROVISION, $_data );
    }

    /**
     * @param Instance $instance
     */
    protected function _provisionStorage( $instance )
    {
        if ( null === ( $_basePath = config( 'dfe.provisioning.local-storage-base-path' ) ) )
        {
            throw new \RuntimeException( 'There is no local storage base configured for this provisioner. Please check configuration.' );
        }

        $_publicPath = rtrim( $_basePath, ' ' . DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . $instance->storage_key_text;
        $_privatePath = $_publicPath . DIRECTORY_SEPARATOR . trim( config( 'dfe.provisioning.private-base-path' ), ' ' . DIRECTORY_SEPARATOR );

        //  Ensure structure...
        foreach ( config( 'dfe.provisioning.pubic-paths' ) as $_path )
        {
            $_check = $_publicPath . DIRECTORY_SEPARATOR . trim( $_path, ' ' . DIRECTORY_SEPARATOR );

            if ( !is_dir( $_check ) && false === mkdir( $_check, 0777, true ) )
            {
                throw new \RuntimeException( 'File system error when creating private path "' . $_check . '".' );
            }
        }

        foreach ( config( 'dfe.provisioning.private-paths' ) as $_path )
        {
            $_check = $_privatePath . DIRECTORY_SEPARATOR . trim( $_path, ' ' . DIRECTORY_SEPARATOR );

            if ( !is_dir( $_check ) && false === mkdir( $_check, 0777, true ) )
            {
                throw new \RuntimeException( 'File system error when creating private path "' . $_check . '".' );
            }
        }

        if ( !is_dir( $_publicPath ) )
        {
            if ( false === mkdir( $_publicPath, 0777, true ) )
            {
                throw new \RuntimeException( 'File system error while provisioning storage. Please check system logs for details.' );
            }
        }
    }

    /**
     * @param string|int $instanceId
     * @param array      $options
     *
     * @return bool
     */
    public function deprovision( $instanceId, $options = [] )
    {
        $_timestamp = microtime( true );
        $_instance = $this->_getInstance( $instanceId );

        $_elapsed = null;
        $_output = [];

        if ( GuestLocations::DFE_CLUSTER == $_instance->guest_location_nbr )
        {
            //	Update the current instance state
            $_instance->updateState( ProvisionStates::DEPROVISIONING );

            $_returnValue = $_virtual->deprovision( $_instance, $options );

            $_elapsed = ( microtime( true ) - $_timestamp );
        }
        else
        {
            $_command =
                config( 'dfe.provisioning.script.deprovisioning' ) .
                escapeshellarg( $_instance->instance_name_text ) . ' ' . escapeshellarg( $_instance->base_image_text );

            //	Update the current instance state
            $_virtual->updateState( ProvisionStates::DEPROVISIONING );

            exec( $_command, $_output, $_returnValue );

            $_elapsed = ( microtime( true ) - $_timestamp );
        }

        if ( 0 != $_returnValue )
        {
            $_virtual->updateState( ProvisionStates::DEPROVISIONING_ERROR );

            $this->_sendNotification(
                $_instance->user,
                'DSP Destroy Failure',
                str_replace(
                    '%%DSP_NAME%%',
                    $_instance->instance_name_text,
                    static::DEPROVISION_FAILURE_TEXT
                )
            );

            return $_output;
        }

        if ( GuestLocations::DFE_CLUSTER == $_instance->guest_location_nbr )
        {
            $_instanceId = $_instance->instance_name_text;
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

        if ( empty( $_instanceId ) )
        {
            //  Failure
            $this->_sendNotification(
                $_instance->user,
                'DSP Destroy Failure',
                'Destroy Failure',
                str_replace(
                    '%%DSP_NAME%%',
                    $_instance->instance_name_text,
                    static::DEPROVISION_FAILURE_TEXT
                )
            );

            return $_output;
        }

        $_instance->provision_ind = 0;
        $_instance->deprovision_ind = 1;
        $_instance->terminate_date = $_instance->end_date = date( 'c' );
        $_instance->instance_id_text = $_instanceId;
        $_instance->state_nbr = ProvisionStates::DEPROVISIONED;
        $_instance->save();

        try
        {
            $this->_sendNotification(
                $_instance->user,
                'DSP Launch Complete',
                str_replace(
                    '%%DSP_NAME%%',
                    $_instance->instance_name_text,
                    static::LAUNCH_SUCCESS_TEXT
                ),
                MailTemplates::PROVISION_COMPLETE
            );
        }
        catch ( \Exception $_ex )
        {
            //	Non-fatal
        }

        return ['instance' => $_instance->getAttributes(), 'log' => $_output];
    }

    /**
     * @param \stdClass|User $user
     * @param string         $subject
     * @param string         $text
     * @param int            $type
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function _sendNotification( $user, $subject, $text, $type = \DreamFactory\Enterprise\Common\Enums\MailTemplates::NOTIFICATION )
    {
        $_data = [
            'subject'         => $subject,
            'first_name_text' => $user->first_name_text,
            'email_text'      => $text,
            'to'              => [$user->email_addr_text => $user->display_name_text],
            'bcc'             => [
                'ops@dreamfactory.com'     => '[DFE] ' . $subject,
                'support@dreamfactory.com' => '[DFE] ' . $subject
            ],
            'from'            => ['no.reply@dreamfactory.com' => 'DreamFactory Enterprise(tm) Console'],
            'type'            => $type,
        ];

        try
        {
            //@todo queue email
            app( 'mailer' )->send( $_data );

            return true;
        }
        catch ( \Exception $_ex )
        {
            throw $_ex;
        }
    }

    /**
     * Creates a snapshot of a fabric-hosted instance
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function launch( $ownerId, $instanceId )
    {
        // TODO: Implement launch() method.
    }

    /**
     * Destroys a DSP
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function destroy( $ownerId, $instanceId )
    {
        // TODO: Implement destroy() method.
    }

    /**
     * Replaces a DSP with an existing snapshot
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     * @param string     $snapshot   The path to the snapshot file
     *
     * @return mixed
     */
    public function replace( $ownerId, $instanceId, $snapshot )
    {
        // TODO: Implement replace() method.
    }

    /**
     * Stops a DSP if supported by host
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function stop( $ownerId, $instanceId )
    {
        // TODO: Implement stop() method.
    }

    /**
     * Starts a DSP if supported by host
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function start( $ownerId, $instanceId )
    {
        // TODO: Implement start() method.
    }

    /**
     * Restart/reboot a DSP if supported by host
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function restart( $ownerId, $instanceId )
    {
        // TODO: Implement restart() method.
    }

    /**
     * Performs a complete wipe of a DSP. The DSP is not destroyed, but the database is completely wiped and recreated as if this were a brand new
     * DSP. Files in the storage area are NOT touched.
     *
     * @param int        $ownerId    The owner of the instance
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function wipe( $ownerId, $instanceId )
    {
        // TODO: Implement wipe() method.
    }
}