<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\ExportJob;

/**
 * Processes queued snapshot requests
 */
class ExportJobHandler extends BaseListener
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a provisioning request
     *
     * @param \DreamFactory\Enterprise\Services\Jobs\ExportJob $job
     *
     * @return mixed
     *
     */
    public function handle(ExportJob $job)
    {
        $this->setLumberjackPrefix('dfe.export');

        $_start = microtime(true);

        $this->debug('>>> export "' . $job->getInstanceId() . '" request received');

        try {
            $job->setResult($_result = Provision::export($job));
            $this->debug('<<< export "' . $job->getInstanceId() . '" request SUCCESS');
        } catch (\Exception $_ex) {
            $this->error('<<< export "' . $job->getInstanceId() . '" request FAILURE: ' . $_ex->getMessage());
            $_result = false;
        }

        $_elapsed = microtime(true) - $_start;
        $this->debug('export complete in ' . number_format($_elapsed, 4) . 's');

        return $_result;
    }

}
