<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Filesystem\Filesystem;

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
     * @type Filesystem
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
     * @param \Illuminate\Filesystem\Filesystem                            $storage
     * @param bool                                                         $deprovision
     */
    public function __construct( Instance $instance, Filesystem $storage = null, $deprovision = false )
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
     * @return Filesystem
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
     * @param Filesystem $storage
     *
     * @return ProvisioningRequest
     */
    public function setStorage( Filesystem $storage )
    {
        $this->_storage = $storage;

        return $this;
    }

}
