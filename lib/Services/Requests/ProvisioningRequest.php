<?php
namespace DreamFactory\Enterprise\Services\Requests;

use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Utility\RemoteInstance;
use DreamFactory\Library\Fabric\Common\Components\FabricObject;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;

class ProvisioningRequest extends FabricObject
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type int The owner of this instance
     */
    protected $_ownerId = null;
    /**
     * @type string The owner's storage key
     */
    protected $_ownerKey = null;
    /**
     * @type string The instance ID
     */
    protected $_instanceId = null;
    /**
     * @type string The name of the instance to provision
     */
    protected $_instanceName;
    /**
     * @type string The instance's storage key
     */
    protected $_instanceKey = null;
    /**
     * @type int The guest location of this instance
     */
    protected $_guestLocation = GuestLocations::DFE_CLUSTER;
    /**
     * @type int The number of gigabytes of ram requested
     */
    protected $_memory = 1;
    /**
     * @type int The number of gigabytes of disk space requested
     */
    protected $_disk = 8;
    /**
     * @type Filesystem The storage file system for this instance
     */
    protected $_storage = null;
    /**
     * @type bool True if a DNS record is to be added for this request
     */
    protected $_dnsRequired = false;
    /**
     * @type string Relative path of instance storage area under ProvisionRequest::$storage
     */
    protected $_storagePath = null;
    /**
     * @type string Relative path of private instance storage area under ProvisionRequest::$storage
     */
    protected $_privatePath = null;
    /**
     * @type Instance|RemoteInstance
     */
    protected $_instance = null;
    /**
     * @type bool True if this request's storage is on a partitioned disk
     */
    protected $_partitioned = true;
    /**
     * @type bool Set to true when request is for a deprovision
     */
    protected $_deprovision = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param Instance|RemoteInstance $instance
     * @param array                   $options
     * @param bool                    $deprovision
     *
     * @return static
     */
    public static function createFromInstance( $instance, $options = [], $deprovision = false )
    {
        $instance = ( $instance instanceOf RemoteInstance ) ? $instance->getInstance() : $instance;

        $_data = [
            'owner-id'       => $instance->user_id,
            'owner-key'      => $instance->user->storage_key_text,
            'instance-name'  => $instance->instance_name_text,
            'instance-key'   => $instance->storage_key_text,
            'cluster-id'     => Config::get( 'dfe.provisioning.default-cluster-id' ),
            'guest-location' => $instance->guest_location_nbr,
            'ram-size'       => IfSet::get( $options, 'ram-size', Config::get( 'dfe.provisioning.default-ram-size' ) ),
            'disk-size'      => IfSet::get( $options, 'disk-size', Config::get( 'dfe.provisioning.default-disk-size' ) ),
            'storage'        => null,
            'deprovision'    => $deprovision,
            'options'        => $options,
        ];

        return new static( $_data );
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->_ownerId;
    }

    /**
     * @param int $ownerId
     *
     * @return ProvisioningRequest
     */
    public function setOwnerId( $ownerId )
    {
        $this->_ownerId = $ownerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerKey()
    {
        return $this->_ownerKey;
    }

    /**
     * @param string $ownerKey
     *
     * @return ProvisioningRequest
     */
    public function setOwnerKey( $ownerKey )
    {
        $this->_ownerKey = $ownerKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceName()
    {
        return $this->_instanceName;
    }

    /**
     * @param string $instanceName
     *
     * @return ProvisioningRequest
     */
    public function setInstanceName( $instanceName )
    {
        $this->_instanceName = $instanceName;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceKey()
    {
        return $this->_instanceKey;
    }

    /**
     * @param string $instanceKey
     *
     * @return ProvisioningRequest
     */
    public function setInstanceKey( $instanceKey )
    {
        $this->_instanceKey = $instanceKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getGuestLocation()
    {
        return $this->_guestLocation;
    }

    /**
     * @param int $guestLocation
     *
     * @return ProvisioningRequest
     */
    public function setGuestLocation( $guestLocation )
    {
        $this->_guestLocation = $guestLocation;

        return $this;
    }

    /**
     * @return int
     */
    public function getMemory()
    {
        return $this->_memory;
    }

    /**
     * @param int $memory
     *
     * @return ProvisioningRequest
     */
    public function setMemory( $memory )
    {
        $this->_memory = $memory;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisk()
    {
        return $this->_disk;
    }

    /**
     * @param int $disk
     *
     * @return ProvisioningRequest
     */
    public function setDisk( $disk )
    {
        $this->_disk = $disk;

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * @param Filesystem $storage
     *
     * @return ProvisioningRequest
     */
    public function setStorage( $storage )
    {
        $this->_storage = $storage;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->_instanceId;
    }

    /**
     * @param string $instanceId
     *
     * @return ProvisioningRequest
     */
    public function setInstanceId( $instanceId )
    {
        $this->_instanceId = $instanceId;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDnsRequired()
    {
        return $this->_dnsRequired;
    }

    /**
     * @param boolean $dnsRequired
     *
     * @return ProvisioningRequest
     */
    public function setDnsRequired( $dnsRequired )
    {
        $this->_dnsRequired = $dnsRequired;

        return $this;
    }

    /**
     * @return string
     */
    public function getStoragePath()
    {
        return $this->_storagePath;
    }

    /**
     * @param string $storagePath
     *
     * @return ProvisioningRequest
     */
    public function setStoragePath( $storagePath )
    {
        $this->_storagePath = $storagePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrivatePath()
    {
        return $this->_privatePath;
    }

    /**
     * @param string $privatePath
     *
     * @return ProvisioningRequest
     */
    public function setPrivatePath( $privatePath )
    {
        $this->_privatePath = $privatePath;

        return $this;
    }

    /**
     * @return RemoteInstance|Instance
     */
    public function getInstance()
    {
        return $this->_instance;
    }

    /**
     * @param RemoteInstance|Instance $instance
     *
     * @return ProvisioningRequest
     */
    public function setInstance( $instance )
    {
        $this->_instance = $instance;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPartitioned()
    {
        return $this->_partitioned;
    }

    /**
     * @param boolean $partitioned
     *
     * @return ProvisioningRequest
     */
    public function setPartitioned( $partitioned )
    {
        $this->_partitioned = $partitioned;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeprovision()
    {
        return $this->_deprovision;
    }

    /**
     * @param boolean $deprovision
     *
     * @return ProvisioningRequest
     */
    public function setDeprovisioning( $deprovision )
    {
        $this->_deprovision = $deprovision;

        return $this;
    }

}
