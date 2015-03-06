<?php
namespace DreamFactory\Enterprise\Common\Services;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * A base class for services that are logger-aware
 */
class BaseService implements LoggerAwareInterface
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use LoggerAwareTrait;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = app( 'log' );
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
