<?php
namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\JobCommand;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

class ProvisionJob extends JobCommand
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'provision';

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

    /**
     * @return string The handler class for this job if different from "[class-name]Handler"
     */
    public function getHandler()
    {
        return 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\ProvisionHandler';
    }
}
