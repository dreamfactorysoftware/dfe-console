<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Commands\ImportJob;
use DreamFactory\Enterprise\Services\Facades\Snapshot;
use DreamFactory\Library\Utility\IfSet;

/**
 * Processes queued requests
 */
class ImportHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a request
     *
     * @param  ImportJob $command
     *
     * @return mixed
     */
    public function handle( ImportJob $command )
    {
        $_options = $command->getOptions();
        \Log::debug( 'dfe: import - begin' );

        try
        {
            $_result = Snapshot::restore( $command->getInstanceId(), $_options['snapshot'], IfSet::get( $_options, 'disk' ) );
            \Log::debug( 'dfe: import - complete' );

            return $_result;
        }
        catch ( \Exception $_ex )
        {
            \Log::error( 'dfe: instance import - failure > ' . $_ex->getMessage() );

            return false;
        }
    }

}
