<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Common\Enums\ServerTypes;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\Server;

class ClusterController extends OpsResourceController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'cluster_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Cluster';
    /** @type string */
    protected $_resource = 'cluster';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Returns an array of the instances assigned to a cluster
     *
     * @param int|string $clusterId The cluster ID
     *
     * @return array
     */
    public function getInstances($clusterId)
    {
        $_instances = [];

        $_servers = $this->_clusterServers($clusterId);

        /** @type Server $_server */
        foreach ($_servers[ServerTypes::WEB] as $_server) {
            if (!empty($_deployed = $_server->instances())) {
                foreach ($_deployed as $_instance) {
                    $_instances[$_instance->instance_name_text] = $_instance->toArray();
                }
            }
        }

        $this->debug('found ' . count($_instances) . ' instance(s)');

        return $_instances;
    }

}
