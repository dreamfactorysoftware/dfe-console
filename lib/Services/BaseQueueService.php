<?php
namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Requests\ProvisioningRequest;
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

/**
 * A service that interacts with a queue
 */
class BaseQueueService
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The queue to use by default
     */
    protected $_queue = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function __construct( $name )
    {
        $this->_queue = App::make( 'queue', $name );
    }

    /**
     * Get the current status of a provisioning request
     *
     * @param string $requestId The job id of the request to check
     *
     * @return mixed
     */
    public function status( $requestId )
    {
    }

    /**
     * @param ProvisioningRequest $request
     *
     * @return string The job id of the request if successful
     */
    public function provision( ProvisioningRequest $request )
    {
    }

    /**
     * @param ProvisioningRequest $request
     *
     * @return bool
     */
    public function deprovision( ProvisioningRequest $request )
    {
    }

    /**
     * @param string $instanceId
     * @param array  $options
     *
     * @return bool
     * @internal param string $instanceName
     */
    public function provision2( $instanceId, $options = [] )
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
     * @param string|int $instanceId
     * @param array      $options
     *
     * @return bool
     */
    public function deprovision2( $instanceId, $options = [] )
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
}