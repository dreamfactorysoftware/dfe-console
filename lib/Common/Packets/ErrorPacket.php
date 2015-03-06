<?php
namespace DreamFactory\Enterprise\Common\Packets;

use Symfony\Component\HttpFoundation\Response;

class ErrorPacket extends BasePacket
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param mixed             $contents
     * @param int               $statusCode
     * @param string|\Exception $message
     *
     * @return array The packetized contents
     */
    public static function make( $contents = null, $statusCode = Response::HTTP_NOT_FOUND, $message = null )
    {
        $_packet = static::_create( false, $contents, $statusCode );

        if ( $message instanceof \Exception )
        {
            $_packet['error'] = array(
                'message' => $message->getMessage(),
                'code'    => $message->getCode()
            );

            if ( Response::HTTP_TEMPORARY_REDIRECT == $message->getCode() && method_exists( $message, 'getRedirectUri' ) )
            {
                $_packet['location'] = $message->getRedirectUri();
            }
        }

        return $_packet;
    }
}