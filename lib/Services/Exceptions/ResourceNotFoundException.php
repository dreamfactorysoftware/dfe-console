<?php
namespace DreamFactory\Enterprise\Services\Exceptions;

use Exception;

/**
 * Generic resource not found
 */
class ResourceNotFoundException extends ProvisioningException
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string    $message
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct( $message = 'Resource not found', $code = 404, Exception $previous = null )
    {
        parent::__construct( $message, $code, $previous );
    }

}