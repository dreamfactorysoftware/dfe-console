<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Filesystems\InstanceFilesystem;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

class ProvisioningRequest
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Instance
     */
    protected $_instance;
    /**
     * @type InstanceFilesystem
     */
    protected $_storage;
    /**
     * @type bool
     */
    protected $_deprovision = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param \DreamFactory\Library\Fabric\Database\Models\Deploy\Instance $instance
     * @param InstanceFilesystem                                           $storage
     * @param bool                                                         $deprovision
     */
    public function __construct( Instance $instance, InstanceFilesystem $storage = null, $deprovision = false )
    {
        $this->_instance = $instance;
        $this->_storage = $storage;
        $this->_deprovision = $deprovision;
    }

    /**
     * @return Instance
     */
    public function getInstance()
    {
        return $this->_instance;
    }

    /**
     * @return InstanceFilesystem
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * @return boolean
     */
    public function isDeprovision()
    {
        return $this->_deprovision;
    }

    /**
     * @param InstanceFilesystem $storage
     *
     * @return ProvisioningRequest
     */
    public function setStorage( InstanceFilesystem $storage )
    {
        $this->_storage = $storage;

        return $this;
    }

}
