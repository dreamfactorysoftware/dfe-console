<?php namespace DreamFactory\Enterprise\Services\Jobs;

use DreamFactory\Enterprise\Common\Jobs\BaseInstanceJob;

class ProvisionJob extends BaseInstanceJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'provision';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array
     */
    protected $packages;

    /**
     * ProvisionJob constructor
     *
     * @param int|string $instanceId
     * @param array      $options
     */
    public function __construct($instanceId, array $options)
    {
        $this->packages = array_get($options, 'packages', []);
        array_forget($options, 'packages');

        parent::__construct($instanceId, $options);
    }

    /**
     * @return array
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param array $packages
     *
     * @return $this
     */
    public function setPackages($packages)
    {
        $this->packages = $packages;

        return $this;
    }
}
