<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Commands\ExportJob;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Providers\SnapshotServiceProvider;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Utility\IfSet;

/**
 * Processes queued snapshot requests
 */
class ExportHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a provisioning request
     *
     * @param  ExportJob $command
     *
     * @return mixed
     */
    public function handle( ExportJob $command )
    {
        $_options = $command->getOptions();
        \Log::debug( 'dfe: ExportJob - begin' );

        try
        {
            //  Create the instance record
            $_instance = app( SnapshotServiceProvider::IOC_NAME )->make( $command->getInstanceId(), $_options );
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'dfe: ExportJob - failure, exception creating instance: ' . $_ex->getMessage() );

            return false;
        }

        try
        {
            $_guest = IfSet::get( $_options, 'guest-location-nbr', config( 'dfe.provisioning.default-guest-location' ) );
            $_provisioner = Provision::getProvisioner( $_guest );

            if ( empty( $_provisioner ) )
            {
                throw new \RuntimeException( 'The provisioner of the request is not valid.' );
            }

            $_result = $_provisioner->provision( new ProvisioningRequest( $_instance ), $_options );

            if ( is_array( $_result ) && $_result['success'] && isset( $_result['elapsed'] ) )
            {
                \Log::debug( 'dfe: completed in ' . number_format( $_result['elapsed'], 4 ) . 's' );
            }

            \Log::debug( 'dfe: ExportJob - complete: ' . print_r( $_result, true ) );

            return $_result;
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'exception during provisioning: ' . $_ex->getMessage() );

            //  Delete instance record...
            if ( !$_instance->delete() )
            {
                throw new \LogicException( 'Unable to remove created instance "' . $_instance->instance_id_text . '".' );
            }
        }

        \Log::debug( 'dfe: ExportJob - fail' );

        return false;
    }

}
