<?php
namespace DreamFactory\Enterprise\Common\Services;

use Doctrine\ODM\CouchDB\Event;
use DreamFactory\Enterprise\Common\Traits\Lumberjack;
use DreamFactory\Library\Fabric\Common\Components\JsonFile;
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

    /**
     * @param mixed $object
     * @param int   $options
     */
    protected function _jsonEncode( $object, $options = JsonFile::DEFAULT_JSON_ENCODE_OPTIONS )
    {
        JsonFile::encode( $object, $options );
    }

    /**
     * @param string $json
     * @param bool   $asArray
     * @param int    $depth
     * @param int    $options
     *
     * @return array|\stdClass
     */
    protected function _jsonDecode( $json, $asArray = true, $depth = 512, $options = 0 )
    {
        return JsonFile::decode( $json, $asArray, $depth, $options );
    }
}
