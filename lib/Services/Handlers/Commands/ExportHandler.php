<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Commands\ExportJob;
use DreamFactory\Enterprise\Services\Facades\Snapshot;

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
    public function handle(ExportJob $command)
    {
        \Log::debug('dfe: ExportJob - begin');

        $_start = microtime(true);

        try {
            //  Get the instance
            $_instance = $this->_findInstance($command->getInstanceId());
        } catch (\Exception $_ex) {
            \Log::error('dfe: ExportJob - failure, invalid instance "' . $command->getInstanceId() . '".');

            return false;
        }

        try {
            $_result = Snapshot::create($_instance->instance_id_text);
            $_elapsed = microtime(true) - $_start;

            \Log::debug('  * completed in ' . number_format($_elapsed, 4) . 's');
            \Log::debug('dfe: ExportJob - complete: ' . print_r($_result, true));

            $command->setResult($_result);

            return $_result;
        } catch (\Exception $_ex) {
            $_elapsed = microtime(true) - $_start;
            \Log::debug('  * completed in ' . number_format($_elapsed, 4) . 's');
            \Log::error('  * exception: ' . $_ex->getMessage());
            \Log::debug('dfe: ExportJob - fail');

            return false;
        }
    }

}
