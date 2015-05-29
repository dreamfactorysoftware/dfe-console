<?php namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * The contract for a single provisioner offering
 */
interface EnterpriseJob
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string|int $clusterId
     *
     * @return string Return the id/name of the cluster involved in this job
     */
    public function getCluster( $clusterId = null );

    /**
     * @param string|int $serverId
     *
     * @return string Return the id/name of the server involved in this job
     */
    public function getServer( $serverId = null );

    /**
     * @return string Return the absolute path of the output file
     */
    public function getOutputFile();
}