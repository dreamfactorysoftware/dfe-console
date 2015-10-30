<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;

us  DreamFactory\Enterprise\Services\Facades\Provision;

/**
 * Processes queued requests
 */
class ImportJobHandler extends BaseListener
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @inheritdoc */
    const LOG_PREFIX = 'dfe:import';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a provisioning request
     *
     * @param \DreamFactory\Enterprise\Services\Jobs\ImportJob $job
     *
     * @return mixed
     *
     */
    public function handle(ImportJob $job)
    {
        $this->registerHandler($job);

        $this->info('import "' . ($_instanceId = $job->getInstanceId()) . '"');

        $this->startTimer();

        try {
            if (false === ($_response = Provision::import($job))) {
                throw new \RuntimeException('Unknown import failure');
            }

            if (true === $_response) {
                $this->notice('Partial import of "' .
                    $_instanceId .
                    '". No portable services are available for "guest-location".');
            }
        } catch (\RuntimeException $_ex) {
            $this->error('[ERROR] ' . $_ex->getMessage());
            !isset($_response) && $_response = false;
        }

        $this->info('instance import complete in ' . number_format($this->getElapsedTime(), 4) . 's');

        return $_response;
    }
}
