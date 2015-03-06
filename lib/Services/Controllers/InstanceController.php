<?php
namespace DreamFactory\Enterprise\Services\Controllers;

use DreamFactory\Enterprise\Common\Http\Controllers\BaseController;
use DreamFactory\Enterprise\Services\Exceptions\ResourceNotFoundException;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Cluster;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;

class InstanceController extends BaseController
{
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
     * @param int|string $clusterId
     *
     * @return array
     * @throws \DreamFactory\Enterprise\Services\Exceptions\ResourceNotFoundException
     */
    public function getEnvironment( $clusterId = null )
    {
        $_clusterId = $clusterId ?: config( 'dfe.provisioning.default-cluster-id' );

        /** @var Cluster $_cluster */
        if ( null === ( $_cluster = Cluster::byNameOrId( $_clusterId )->first() ) )
        {
            throw new ResourceNotFoundException( 'The cluster id specified was not found.' );
        }

        $_result = $_cluster->toArray();
        $_servers = $_cluster->servers();

        if ( $_servers->count() )
        {
            /** @type Server $_server */
            foreach ( $_servers as $_server )
            {
                $_result['servers'][$_server->server_id_text] = $_server->toArray();
            }
        }

        return $_result;
    }
}