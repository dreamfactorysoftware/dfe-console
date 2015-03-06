<?php
namespace DreamFactory\Enterprise\Services\Contracts;

use DreamFactory\Enterprise\Services\Requests\JobRequest;

/**
 * Describes an object that can queue fabric stuff
 */
interface FabricQueue
{
    /**
     * Queues $request onto the queue named $queue
     *
     * @param mixed      $requestType The type of request
     * @param JobRequest $request     The job request
     *
     * @return $this
     */
    public function queue( $requestType, JobRequest $request );

}