<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;

/**
 * Processes queued requests
 */
class ImportJobHandler extends BaseListener
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const LOG_PREFIX = 'dfe.import';

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
    public function handle(ImportJob $job)
    {
        $_start = microtime(true);
        $_instanceId = $job->getInstanceId();

        $this->debug('>>> import "' . $_instanceId . '" request received');

        try {
            if (false === ($_response = Provision::import($job))) {
                throw new \RuntimeException('import failure');
            }

            $this->info('import complete in ' . number_format(microtime(true) - $_start, 4) . 's');
            $this->debug('<<< import "' . $_instanceId . '" request SUCCESS');
        } catch (\Exception $_ex) {
            $this->error('<<< import "' . $_instanceId . '" request FAILURE: ' . $_ex->getMessage());
            $_response = false;
        }

        return $_response;
    }
}
