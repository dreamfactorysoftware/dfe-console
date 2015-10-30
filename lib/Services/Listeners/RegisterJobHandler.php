<?php namespace DreamFactory\Enterprise\Services\Listeners;

use DreamFactory\Enterprise\Common\Listeners\BaseListener;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Services\Jobs\RegisterJob;
use Illuminate\Http\Response;

us  DreamFactory\Enterprise\Common\Packets\ErrorPacket;

/**
 * Handles registration requests
 */
class RegisterJobHandler extends BaseListener
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a request
     *
     * @param RegisterJob $command
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(RegisterJob $command)
    {
        $_key = config('dfe.security.console-api-key');

        try {
            $_owner = $command->getOwnerInfo();

            //  Generate the key
            $_key = AppKey::createKey($_owner->id, $_owner->type, ['server_secret' => $_key]);

            $this->debug('[dfe:register] Successfully created app key "' . $_key->client_id . '"');

            $_result = SuccessPacket::make($_key->toArray(), Response::HTTP_CREATED);
        } catch (\Exception $_ex) {
            $this->error('[dfe:register] Exception while creating key: ' . $_ex->getMessage());

            $_result = ErrorPacket::create(Response::HTTP_BAD_REQUEST, $_ex);
        }

        $command->publishResult($command->getJobId(), $_result);

        return $_result;
    }
}
