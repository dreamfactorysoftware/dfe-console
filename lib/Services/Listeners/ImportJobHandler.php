<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Database\Models\Instance;
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
            //  Instance cannot exist
            if (null !== ($_instance = Instance::byNameOrId($_instanceId)->first())) {
                throw new \LogicException('Instance "' . $_instanceId . '" already exists.');
            }

            $job->setResult($_result = Provision::import($job));
            $this->debug('<<< import "' . $_instanceId . '" request SUCCESS');
        } catch (\Exception $_ex) {
            $this->error('<<< import "' . $_instanceId . '" request FAILURE: ' . $_ex->getMessage());
            $_result = false;
        }

        $_elapsed = microtime(true) - $_start;
        $this->debug('import complete in ' . number_format($_elapsed, 4) . 's');

        return $_result;
    }
}
