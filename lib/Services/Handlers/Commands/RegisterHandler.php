<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Lumberjack;
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

    use EntityLookup, Lumberjack;

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
        $this->setLumberjackPrefix('dfe:register');

        $_key = config('dfe.security.console-api-key');

        try {
            $_owner = $command->getOwnerInfo();

            //  Generate the key
            $_key = AppKey::createKey($command->getOwnerId(), $command->getOwnerType(), ['server_secret' => $_key]);

            $this->debug('successfully created app key "' . $_key->client_id . '"');

            $_result = SuccessPacket::make($_key->toArray(), Response::HTTP_CREATED);
        } catch (\Exception $_ex) {
            $this->error('exception while creating key: ' . $_ex->getMessage());

            $_result = ErrorPacket::create(Response::HTTP_BAD_REQUEST, $_ex);
        }

        $command->setResult($_result);

        return $_result;
    }
}
