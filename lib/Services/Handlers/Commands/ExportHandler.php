<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Lumberjack;
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

    use EntityLookup, Lumberjack;

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
        $this->setLumberjackPrefix('dfe.export');

        $_start = microtime(true);

        try {
            //  Get the instance
            $_instance = $this->_findInstance($command->getInstanceId());
        } catch (\Exception $_ex) {
            $this->error('invalid instance "' . $command->getInstanceId() . '".');

            return false;
        }

        try {
            $_result = Snapshot::create($_instance->instance_id_text);
            $command->setResult($_result);
        } catch (\Exception $_ex) {
            $_result = false;
            $this->error('exception during export: ' . $_ex->getMessage());
        }

        $_elapsed = microtime(true) - $_start;
        $this->debug('export complete in ' . number_format($_elapsed, 4) . 's');

        return $_result;
    }

}
