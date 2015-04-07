<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Commands\ExportJob;
use DreamFactory\Enterprise\Services\Facades\Snapshot;
use DreamFactory\Enterprise\Services\Utility\InstanceMetadata;

/**
 * Processes queued snapshot requests
 */
class ExportHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

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
            //  Get the instance
            $_instance = $this->_findInstance( $command->getInstanceId() );
            $_md = InstanceMetadata::createFromInstance( $_instance );
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'dfe: ExportJob - failure, invalid instance "' . $command->getInstanceId() . '".' );

            return false;
        }

        try
        {
            $_result = Snapshot::create( $_instance->instance_id_text );

            if ( is_array( $_result ) && $_result['success'] && isset( $_result['elapsed'] ) )
            {
                \Log::debug( 'dfe: completed in ' . number_format( $_result['elapsed'], 4 ) . 's' );
            }

            \Log::debug( 'dfe: ExportJob - complete: ' . print_r( $_result, true ) );

            return $_result;
        }
        catch ( \Exception $_ex )
        {
            \Log::error( '  * exception: ' . $_ex->getMessage() );
            \Log::debug( 'dfe: ExportJob - fail' );

            return false;
        }
    }

}
