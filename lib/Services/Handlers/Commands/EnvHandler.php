<?php namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Commands\EnvJob;
use Illuminate\Http\Response;

/**
 * Processes queued environment generation requests
 */
class EnvHandler
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
     * @param EnvJob $command
     *
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle( EnvJob $command )
    {
        \Log::debug( 'dfe: env - begin' );

        if ( file_exists( $command->get))

        try
        {
            $_result = SuccessPacket::make( $_key->toArray(), Response::HTTP_CREATED );
        }
        catch ( \Exception $_ex )
        {
            $_result = ErrorPacket::create( Response::HTTP_BAD_REQUEST );
        }

        $command->setResult( $_result );

        return $command;
    }
}
