<?php
namespace DreamFactory\Enterprise\Common\Packets;

use Symfony\Component\HttpFoundation\Response;

class SuccessPacket extends BasePacket
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param int   $statusCode
     * @param mixed $contents
     *
     * @return array The packetized contents
     */
    public static function make( $contents = null, $statusCode = Response::HTTP_OK )
    {
        return static::_create( true, $contents, $statusCode );
    }

}