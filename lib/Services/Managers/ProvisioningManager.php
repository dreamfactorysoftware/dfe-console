<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

class ProvisioningManager extends BaseManager
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function host( $name = null )
    {
        return $this->resolve( $name );
    }

    /**
     * Get a filesystem instance.
     *
     * @param  string $name
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function drive( $name = null )
    {
        return $this->disk( $name );
    }

    /**
     * Get a filesystem instance.
     *
     * @param  string $name
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk( $name = null )
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->disks[$name] = $this->get( $name );
    }

    /**
     * Attempt to get the disk from the local cache.
     *
     * @param  string $name
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function get( $name )
    {
        return isset( $this->disks[$name] ) ? $this->disks[$name] : $this->resolve( $name );
    }

    /**
     * Get the provisioner configuration.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfig( $name )
    {
        return $this->_app['config']['dfe.provisioners.' . $name];
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string   $driver
     * @param  \Closure $callback
     *
     * @return $this
     */
    public function extend( $driver, Closure $callback )
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call( $method, $parameters )
    {
        return call_user_func_array( array($this->disk(), $method), $parameters );
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