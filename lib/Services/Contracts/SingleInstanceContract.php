<?php
namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * Describes a virtual server/single instance interface
 */
interface SingleInstanceContract extends InstanceControlContract
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Stop an instance if supported by host
     *
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function stop( $instanceId );

    /**
     * Start an instance if supported by host
     *
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function start( $instanceId );

    /**
     * Restart/reboot an instance if supported by host
     *
     * @param int|string $instanceId The instance ID
     *
     * @return mixed
     */
    public function restart( $instanceId );
}
