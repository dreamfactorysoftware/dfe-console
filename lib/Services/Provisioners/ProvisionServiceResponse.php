<?php namespace DreamFactory\Enterprise\Services\Provisioners;

c ass ProvisionServiceResponse extends BaseResponse
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type \DreamFactory\Enterprise\Database\Models\Instance
     */
    protected
    $instance;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return \DreamFactory\Enterprise\Database\Models\Instance
     */
    public
    function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return ProvisionServiceResponse
     */
    public
    function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }
}
