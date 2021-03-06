<?php namespace DreamFactory\Enterprise\Services\Jobs;

use DreamFactory\Enterprise\Common\Jobs\BaseInstanceJob;
use DreamFactory\Enterprise\Common\Provisioners\PortableServiceRequest;

/**
 * A base class for portable jobs
 */
class PortableJob extends BaseInstanceJob
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type mixed The job target
     */
    protected $target;
    /**
     * @type mixed Where to send the output
     */
    protected $outputFile;
    /**
     * @type bool If true, the default, $target is presumed to be an indirect pointer (snapshot-id for instance)
     */
    protected $indirect = true;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new portability job
     *
     * @param PortableServiceRequest $request The request
     */
    public function __construct(PortableServiceRequest $request)
    {
        $this->target = $request->getTarget();
        $this->outputFile = $request->get('output-file');
        $this->ownerId = $request->get('owner-id');
        $this->ownerType = $request->get('owner-type');
        $this->indirect = $request->get('indirect', true);

        parent::__construct($request->getInstanceId(), $request->toArray());
    }

    /**
     * @return boolean
     */
    public function isIndirect()
    {
        return $this->indirect;
    }

    /**
     * @param boolean $indirect
     *
     * @return $this
     */
    public function setIndirect($indirect)
    {
        $this->indirect = $indirect;

        return $this;
    }

    /**
     * @param mixed $target
     *
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return mixed
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }

    /**
     * @param mixed $outputFile
     *
     * @return $this
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;

        return $this;
    }
}
