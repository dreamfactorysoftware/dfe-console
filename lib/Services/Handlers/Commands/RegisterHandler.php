<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Services\Commands\RegisterJob;
use Illuminate\Http\Response;

/**
 * Processes queued registration requests
 */
class RegisterHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

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
        \Log::debug('dfe: register - begin');

        $_key = config('dfe.security.console-api-key');

        try {
            $_owner = $command->getOwnerInfo();

            //  Generate the key
            $_key = AppKey::createKey($_owner->id, $_owner->type, ['server_secret' => $_key]);

            \Log::debug('dfe: register - complete');

            $_result = SuccessPacket::make($_key->toArray(), Response::HTTP_CREATED);
        } catch (\Exception $_ex) {
            $_result = ErrorPacket::create(Response::HTTP_BAD_REQUEST);
        }

        $command->setResult($_result);

        return $_result;
    }
}
