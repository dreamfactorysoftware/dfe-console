<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;

/**
 * Processes queued requests
 */
class ImportJobHandler extends BaseListener
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a request
     *
     * @param  ImportJob $job
     *
     * @return mixed
     */
    public function handle(ImportJob $job)
    {
        $this->setLumberjackPrefix('dfe.import');

        $_start = microtime(true);

        $this->debug('>>> import "' . $job->getInstanceId() . '" request received');

        try {
            $job->setResult($_result = \Provision::import($job));
            $this->debug('import "' . $job->getInstanceId() . '" request SUCCESS');
        } catch (\Exception $_ex) {
            $this->error('import "' . $job->getInstanceId() . '" request FAILURE: ' . $_ex->getMessage());
            $_result = false;
        }

        $_elapsed = microtime(true) - $_start;
        $this->debug('<<< import complete in ' . number_format($_elapsed, 4) . 's');

        return $_result;
    }

}
