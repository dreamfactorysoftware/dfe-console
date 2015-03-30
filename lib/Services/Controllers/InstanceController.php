<?php
namespace DreamFactory\Enterprise\Services\Controllers;

use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Console\Http\Controllers\FactoryController;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Cluster;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;

class InstanceController extends FactoryController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return array All clusters
     */
    public function index()
    {
        $_response = [];

        /** @type Cluster $_cluster */
        foreach ( Cluster::all() as $_cluster )
        {
            $_response[$_cluster->cluster_id_text] = $_cluster->toArray();
            $_response[$_cluster->cluster_id_text]['servers'] = [];

            $_servers = $_cluster->servers();

            if ( $_servers->count() )
            {
                /** @type Server $_server */
                foreach ( $_servers as $_server )
                {
                    $_response[$_cluster->cluster_id_text]['servers'][$_server->server_id_text] = $_server->toArray();
                }
            }
        }

        return $_response;
    }

    /**
     * @param int|string $instanceId
     *
     * @return array
     * @throws \DreamFactory\Enterprise\Services\Exceptions\ResourceNotFoundException
     *
     */
    public function getMetadata( $instanceId )
    {
        $_instance = $this->_validateInstance( $instanceId );

        if ( !$_instance->user )
        {
            throw new \RuntimeException( 'The user for instance "' . $instanceId . '" was not found.' );
        }

        return $_instance->getMetadata();
    }
}
