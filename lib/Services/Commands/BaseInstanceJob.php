<?php
namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\JobCommand;
use DreamFactory\Enterprise\Database\Models\Instance;

/**
 * A base class for all DFE instance-related "job" commands (non-console)
 */
abstract class BaseInstanceJob extends JobCommand
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const DEFAULT_HANDLER_NAMESPACE = 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Instance
     */
    protected $_instanceId;
    /**
     * @type array
     */
    protected $_options = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new command instance.
     *
     * @param string $instanceId The instance to provision
     * @param array  $options    Provisioning options
     */
    public function __construct( $instanceId, $options = [] )
    {
        $this->_instanceId = $instanceId;
        $this->_options = $options;
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->_instanceId;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
