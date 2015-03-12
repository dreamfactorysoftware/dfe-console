<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Contracts\Filesystem\Filesystem;

class ProvisioningRequest
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Instance The instance to provision
     */
    protected $_instance;
    /**
     * @type Filesystem The instance's local file system
     */
    protected $_storage;
    /**
     * @type bool True if this is a DE-provision
     */
    protected $_deprovision = false;
    /**
     * @type bool True if the $request should be forced
     */
    protected $_forced = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param \DreamFactory\Library\Fabric\Database\Models\Deploy\Instance $instance
     * @param Filesystem                                                   $storage
     * @param bool                                                         $deprovision
     * @param bool                                                         $force
     */
    public function __construct( Instance $instance, Filesystem $storage = null, $deprovision = false, $force = false )
    {
        $this->_instance = $instance;
        $this->_storage = $storage;
        $this->_deprovision = $deprovision;
        $this->_forced = $force;
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

    /**
     * @return boolean
     */
    public function isForced()
    {
        return $this->_forced;
    }

    /**
     * @param boolean $forced
     *
     * @return ProvisioningRequest
     */
    public function setForced( $forced )
    {
        $this->_forced = $forced;

        return $this;
    }

}
