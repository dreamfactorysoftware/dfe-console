<?php
namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use Carbon\Carbon;
use DreamFactory\Enterprise\Common\Enums\AppKeyClasses;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Services\Commands\RegisterJob;
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

        $_key = config( 'dfe.console-key' );

        $_ownerId = $command->getOwnerId();
        $_ownerType = $command->getOwnerType();
        $_entityType = $command->getEntityType();

        //  Generate the keys
        $_clientId = hash_hmac( config( 'dfe.signature-method', ConsoleDefaults::SIGNATURE_METHOD ), str_random( 40 ), $_key );
        $_clientSecret = hash_hmac( config( 'dfe.signature-method', ConsoleDefaults::SIGNATURE_METHOD ), str_random( 40 ), $_key . $_clientId );

        $_result = AppKey::insert(
            array(
                'owner_id'       => $_ownerId,
                'owner_type_nbr' => $_ownerType,
                'key_class_text' => AppKeyClasses::make( $_entityType ),
                'client_id'      => $_clientId,
                'client_secret'  => $_clientSecret,
                'server_secret'  => $_key,
                'created_at'     => new Carbon(),
            )
        );

        if ( !$_result )
        {
            abort( Response::HTTP_INTERNAL_SERVER_ERROR, 'Could not save new keys to database.' );
        }

        \Log::debug( 'dfe: register - complete' );

        $command->setResult(
            'Client registered successfully.' . PHP_EOL .
            '  * Client ID:     ' . $_clientId
        );

        return true;
    }
}
