<?php
namespace DreamFactory\Enterprise\Common\Services;

use Doctrine\ODM\CouchDB\Event;
use DreamFactory\Enterprise\Common\Traits\Lumberjack;
use Psr\Log\LoggerInterface;

/**
 * A base class for services that are logger-aware
 */
class BaseService implements LoggerInterface
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use Lumberjack;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * Perform any service initialization
     */
    public function boot()
    {
        $this->logger = app( 'log' );
    }

}
