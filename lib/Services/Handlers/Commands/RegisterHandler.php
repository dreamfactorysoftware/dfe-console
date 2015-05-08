<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Commands\RegisterJob;
use DreamFactory\Library\Fabric\Database\Enums\OwnerTypes;
use DreamFactory\Library\Fabric\Database\Models\Deploy\AppKey;
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
     */
    public function handle( RegisterJob $command )
    {
        \Log::debug( 'dfe: register - begin' );

        $_key = config( 'dfe.console-api-key' );

        try
        {
            $_ownerId = $command->getOwnerId();
            $_ownerType = $command->getOwnerType();
            $_owner = OwnerTypes::getOwner( $_ownerId, $_ownerType );

            //  Generate the key
            $_key = AppKey::createKey( $_owner->id, $_ownerType, ['server_secret' => $_key] );

            \Log::debug( 'dfe: register - complete' );

            $_result = 'Client registered successfully.' . PHP_EOL . '  * Client ID:     ' . $_key->client_id;

            if ( PHP_SAPI == 'cli' )
            {
                echo $_result . PHP_EOL;
            }
            else
            {
                $command->setResult( SuccessPacket::make( $_key->toArray(), Response::HTTP_CREATED ) );
            }
        }
        catch ( \Exception $_ex )
        {
            if ( PHP_SAPI == 'cli' )
            {
                echo 'Error during registration: ' . $_ex->getMessage() . PHP_EOL;
            }
            else
            {
                $command->setResult( ErrorPacket::make( null, Response::HTTP_BAD_REQUEST ) );
            }

            throw $_ex;
        }

        return true;
    }
}
