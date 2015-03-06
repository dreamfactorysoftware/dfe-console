<?php
namespace DreamFactory\Enterprise\Services\Contracts\Instance;

use Symfony\Component\HttpFoundation\Request;

/**
 * Describes a service that can communicate with a hosted DSP
 */
interface Broker
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Retrieve the latest instance status package
     *
     * @return array
     */
    public function instanceStatus();

    /**
     * Retrieve the latest instance metrics package
     *
     * @return array
     */
    public function instanceMetrics();

    /**
     * Process an enterprise console request
     *
     * @param string $verb
     * @param array  $payload
     * @param array  $options
     *
     * @return mixed
     */
    public function consoleRequest( $verb = Request::METHOD_GET, $payload = [], $options = [] );
}