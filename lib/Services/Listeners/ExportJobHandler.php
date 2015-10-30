<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Services\Jobs\ExportJob;

us  DreamFactory\Enterprise\Services\Facades\Provision;

/**
 * Processes queued snapshot requests
 */
class ExportJobHandler extends BaseListener
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const LOG_PREFIX = 'dfe.export';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a provisioning request
     *
     * @param \DreamFactory\Enterprise\Services\Jobs\ExportJob $job
     *
     * @return array Array version of the snapshot manifest
     *
     */
    public function handle(ExportJob $job)
    {
        $this->registerHandler($job);

        $this->info('export "' . ($_instanceId = $job->getInstanceId()) . '"');

        $this->startTimer();

        try {
            if (false === ($_response = Provision::export($job))) {
                throw new \RuntimeException('Unknown import failure');
            }
        } catch (\RuntimeException $_ex) {
            $this->error('[ERROR] ' . $_ex->getMessage());
            !isset($_response) && $_response = false;
        }

        $this->info('request complete in ' . number_format($this->getElapsedTime(), 4) . 's');

        return $_response;
    }
}
