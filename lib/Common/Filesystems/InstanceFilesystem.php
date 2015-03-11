<?php
namespace DreamFactory\Enterprise\Common\Filesystems;

use Illuminate\Filesystem\Filesystem;

/**
 * InstanceFilesystem
 */
class InstanceFilesystem extends Filesystem
{
    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @type string The relative path to the owner's private path root
     */
    protected $_privateOwnerPath;
    /**
     * @type string The relative path to the instance's private path root
     */
    protected $_privateInstancePath;
    /**
     * @type bool Determines layout of instance storage area
     */
    protected $_partitioned = true;

    //*************************************************************************
    //* Methods
    //*************************************************************************

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
     * @return InstanceFilesystem
     */
    public function setPartitioned( $partitioned )
    {
        $this->_partitioned = $partitioned;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateOwnerPath()
    {
        return $this->_privateOwnerPath;
    }

    /**
     * @param string $privateOwnerPath
     *
     * @return InstanceFilesystem
     */
    public function setPrivateOwnerPath( $privateOwnerPath )
    {
        $this->_privateOwnerPath = trim( $privateOwnerPath, DIRECTORY_SEPARATOR . ' ' );

        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateInstancePath()
    {
        return $this->_privateInstancePath;
    }

    /**
     * @param string $privateInstancePath
     *
     * @return InstanceFilesystem
     */
    public function setPrivateInstancePath( $privateInstancePath )
    {
        $this->_privateInstancePath = trim( $privateInstancePath, DIRECTORY_SEPARATOR . ' ' );

        return $this;
    }

}
