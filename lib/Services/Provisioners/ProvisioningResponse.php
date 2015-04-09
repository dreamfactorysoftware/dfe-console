<?php
namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Contracts\PrivatePathAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Packets\BasePacket;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Contracts\Filesystem\Filesystem;

class ProvisioningResponse
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
    /**
     * @type ResourceProvisioner|PrivatePathAware
     */
    protected $_storageProvisioner;

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
     * @param bool $createIfNull
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getStorage( $createIfNull = true )
    {
        //  Use requested file system if one...
        if ( null === $this->_storage && $createIfNull )
        {
            $this->setStorage(
                $_storage = $this->getInstance()->getRootStorageMount()
            );
        }

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

    /**
     * @return ResourceProvisioner|PrivatePathAware
     */
    public function getStorageProvisioner()
    {
        return $this->_storageProvisioner;
    }

    /**
     * @param ResourceProvisioner $storageProvisioner
     *
     * @return ProvisioningRequest
     */
    public function setStorageProvisioner( $storageProvisioner )
    {
        $this->_storageProvisioner = $storageProvisioner;

        return $this;
    }
}
