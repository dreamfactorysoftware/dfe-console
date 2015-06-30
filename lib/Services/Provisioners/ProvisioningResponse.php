<?php namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Traits\HasResults;
use Illuminate\Http\Request;

class ProvisioningResponse
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use HasResults;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type bool Self-describing
     */
    protected $success = false;
    /**
     * @type mixed|null The output, if any, of the provisioning request
     */
    protected $output;
    /**
     * @type ProvisioningRequest The original request
     */
    protected $request;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Success response factory
     *
     * @param Request    $request
     * @param mixed|null $results Any results produced
     * @param mixed|null $output
     *
     * @return static
     */
    public static function success($request, $results = null, $output = null)
    {
        return new static($request, true, $results, $output);
    }

    /**
     * Failure response factory
     *
     * @param Request    $request
     * @param mixed|null $results Any results produced
     * @param mixed|null $output
     *
     * @return static
     */
    public static function failure($request, $results = null, $output = null)
    {
        return new static($request, false, $results, $output);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param bool                     $success
     * @param mixed|null               $results Any results produced
     * @param mixed|null               $output
     */
    protected function __construct(Request $request, $success, $results = null, $output = null)
    {
        $this->success = !!$success;
        $this->request = $request;
        $this->output = $output;

        $this->setResult($results);
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return mixed|null
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return ProvisioningRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \DreamFactory\Enterprise\Database\Models\Instance
     */
    public function getInstance()
    {
        return $this->request->getInstance();
    }
}
