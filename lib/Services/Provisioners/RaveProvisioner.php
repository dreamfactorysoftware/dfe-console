<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Mail\Message;

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
    protected function _doProvision( $request )
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

            \Mail::send(
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

            //  Force-kill anything we made before blowing up
            $request->setForced( true );
//            $this->_deprovisionStorage( $request );
//            $this->_deprovisionInstance( $request );

            \Mail::send(
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
    protected function _doDeprovision( $request )
    {
        $_output = [];
        $_success = $_result = false;
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

            //  It worked!
            $_success = true;
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

            //  No worky
            $_success = false;
        }

        return ['success' => $_success, 'instance' => $_instance->toArray(), 'log' => $_output, 'result' => $_result];
    }

    /**
     * @param ProvisioningRequest $request
     *
     * @return Filesystem
     */
    protected function _provisionStorage( $request )
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

        //  Do it!
        Provision::resolveStorage( 'rave' )->provision( $request );

        return $_filesystem;
    }

    /**
     * @param ProvisioningRequest $request
     *
     * @return bool
     */
    protected function _deprovisionStorage( $request )
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

        //  Do it!
        Provision::resolveStorage( 'rave' )->deprovision( $request );

        return $_filesystem;
    }

    /**
     * @param ProvisioningRequest $request
     *
     * @return array
     */
    protected function _provisionInstance( $request )
    {
        //	Clean up that nasty name...
        $_instance = $request->getInstance();
        $_dbName = $_instance->db_name_text ?: str_replace( '-', '_', $_instance->instance_name_text );
        $_storageKey = $_instance->storage_id_text;
        $_storage = $request->getStorage();
        $_storagePath = null;
        $_privatePath = $request->get( 'private-path' );
        $_relativePrivatePath = str_replace( $_storagePath, null, $_privatePath );
        $_dbConfigFile = $_relativePrivatePath . DIRECTORY_SEPARATOR . $_name . '.database.config.php';
        $_instanceMetadata = $_relativePrivatePath . DIRECTORY_SEPARATOR . $_name . '.json';

        //	Make sure the user name is kosher
        list( $_dbUser, $_dbPassword ) = $this->_generateDbUser( $_name );

        try
        {
            $_dbConfig = $this->_getDatabaseConfig( $_dbName );

            //	1. Create database
            if ( !$this->_createDatabase( $_dbConfig['server'], $_dbName ) )
            {
                \Log::error( 'Unable to create database "' . $_dbName . '"' );

                return false;
            }

            //	2. Grant privileges
            if ( !$this->_grantPrivileges( $_dbConfig['server'], $_dbName, $_dbUser, $_dbPassword ) )
            {
                try
                {
                    //	Try and get rid of the database we created
                    $this->_dropDatabase( $_dbConfig['server'], $_dbName );
                }
                catch ( \Exception $_ex )
                {
                    \Log::error( 'Exception dropping database: ' . $_ex->getMessage() );
                }

                return false;
            }
        }
        catch ( \Exception $_ex )
        {
            throw new ProvisioningException( $_ex->getMessage(), $_ex->getCode() );
        }

        //	3. Create files in storage
        try
        {
            //	Create database config file...
            $_date = date( 'c' );

            $_php = <<<PHP
<?php
/**
 * **** DO NOT MODIFY THIS FILE ****
 * **** CHANGES WILL BREAK YOUR INSTANCE AND MAY BE OVERWRITTEN AT ANY TIME ****
 * @(#)\$Id: database.config.php; v2.0.0-{$_dbName} {$_date} \$
 */
return array(
	'connectionString'      => 'mysql:host={$_dbConfig['host']};port={$_dbConfig['port']};dbname={$_dbName}',
	'username'              => '{$_dbUser}',
	'password'              => '{$_dbPassword}',
	'emulatePrepare'        => true,
	'charset'               => 'utf8',
	'schemaCachingDuration' => 3600,
);
PHP;

            if ( !$_storage->put( $_dbConfigFile, $_php ) )
            {
                \Log::error( 'Error writing database configuration file: ' . $_dbConfigFile );

                return false;
            }
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'Exception prepping storage: ' . $_ex->getMessage() );
            $this->_dropDatabase( $_dbConfig['server'], $_dbName );
            FileSystem::rmdir( $_storagePath, true );

            return false;
        }

        $_host = $_name . '.' . config( 'dfe.provisioning.default-dns-zone' ) . '.' . config( 'dfe.provisioning.default-dns-domain' );

        //	Update instance with new provision info
        try
        {
            $this->fill(
                [
                    'guest_location_nbr' => GuestLocations::DFE_CLUSTER,
                    'instance_id_text'   => $_name,
                    'instance_name_text' => $_name,
                    'db_host_text'       => $_dbConfig['host'],
                    'db_port_nbr'        => $_dbConfig['port'],
                    'db_name_text'       => $_dbName,
                    'db_user_text'       => $_dbUser,
                    'db_password_text'   => $_dbPassword,
                    'base_image_text'    => 'fabric.standard',
                    'public_host_text'   => $_host,
                    'ready_state_nbr'    => 0, //   Admin Required
                    'state_nbr'          => ProvisionStates::PROVISIONED,
                    'platform_state_nbr' => 0, //   Not Activated
                    'vendor_state_nbr'   => ProvisionStates::PROVISIONED,
                    'vendor_state_text'  => 'running',
                    'start_date'         => date( 'c' ),
                    'end_date'           => null,
                    'terminate_date'     => null,
                    'provision_ind'      => 1,
                    'deprovision_ind'    => 0,
                    'cluster_id'         => $_instance->cluster_id,
                ]
            );

            $this->save();
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Exception while storing new instance data: ' . $_ex->getMessage() );
        }

        $_md = [];

        try
        {
            $_md = $this->getMetadata( $_instance );

            if ( !$_storage->put( $_instanceMetadata, Json::encode( $_md ) ) )
            {
                \Log::error( 'Error writing instance metadata file: ' . $_dbConfigFile );
            }
        }
        catch ( \Exception $_ex )
        {
            //  Don't stop for me...
        }

        //  Fire off a "launch" event...
        \Event::fire( 'dfe.launch', [$this, $_md] );

        return [
            'host'                => $_host,
            'storage_key'         => $_storageKey,
            'blob_path'           => $_storagePath,
            'storage_path'        => $_storagePath,
            'private_path'        => $_privatePath,
            'snapshot_path'       => $_privatePath . DIRECTORY_SEPARATOR . 'snapshots',
            'db_host'             => $_dbConfig['host'],
            'db_port'             => $_dbConfig['port'],
            'db_name'             => $_dbName,
            'db_user'             => $_dbUser,
            'db_password'         => $_dbPassword,
            'db_config_file_name' => $_dbConfigFile,
            'cluster'             => $this->cluster_id,
            'metadata'            => $_md,
        ];

    }

    /**
     * @param ProvisioningRequest $request
     *
     * @return bool
     */
    protected function _deprovisionInstance( $request )
    {

    }

}