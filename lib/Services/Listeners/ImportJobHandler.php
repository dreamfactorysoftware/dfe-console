<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Common\Traits\Notifier;
use DreamFactory\Enterprise\Console\Enums\ConsoleOperations;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;

/**
 * Processes queued requests
 */
class ImportJobHandler extends BaseListener
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use Notifier;

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
        $_owner = $job->getOwner();

        $this->startTimer();

        try {
            if (false === ($_response = Provision::import($job))) {
                throw new \RuntimeException('Unknown import failure');
            }

            if (null === ($_instance = Instance::byNameOrId($_instanceId)->first())) {
                throw new \RuntimeException('Instance not found');
            }

            if (true === $_response) {
                $this->notice('Partial import of "' . $_instanceId . '". No portable services are available for "guest-location".');
            }

            //  Let the user know...
            $this->notifyJobOwner(ConsoleOperations::IMPORT,
                $_owner->email_addr_text,
                trim($_owner->first_name_text . ' ' . $_owner->last_name_text),
                [
                    'instance' => Instance::byNameOrId($_instanceId)->first(),
                ]);
        } catch (\RuntimeException $_ex) {
            $this->error('[ERROR] ' . $_ex->getMessage());
            !isset($_response) && $_response = false;

            //  Let the user know...
            $this->notifyJobOwner(ConsoleOperations::IMPORT,
                $_owner->email_addr_text,
                trim($_owner->first_name_text . ' ' . $_owner->last_name_text),
                [
                    'instance'      => false,
                    'instanceUrl'   => false,
                    'headTitle'     => 'Import Failure',
                    'contentHeader' => 'Your import did not complete',
                ]);
        }

        $this->info('instance import complete in ' . number_format($this->getElapsedTime(), 4) . 's');

        return $_response;
    }
}
