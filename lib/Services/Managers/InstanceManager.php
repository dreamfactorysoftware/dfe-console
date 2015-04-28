<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\Factory;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Common\Traits\StaticComponentLookup;
use DreamFactory\Enterprise\Services\Exceptions\DuplicateInstanceException;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Library\Fabric\Database\Enums\ProvisionStates;
use DreamFactory\Library\Fabric\Database\Enums\ServerTypes;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\InstanceGuest;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Creates and manages instances
 */
class InstanceManager extends BaseManager implements Factory
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use StaticComponentLookup, InstanceValidation;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Constructor
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param Instance[]                                   $instances
     */
    public function __construct( $app, array $instances = [] )
    {
        parent::__construct( $app );

        !empty( $instances ) && $this->registerInstances( $instances );
    }

    /**
     * Register instances
     *
     * @param Instance[] $instances [:tag => instance,]
     *
     * @return $this
     */
    public function registerInstances( array $instances )
    {
        foreach ( $instances as $_tag => $_instance )
        {
            $this->registerInstance( $_tag, $_instance );
        }

        return $this;
    }

    /**
     * Register instance
     *
     * @param string   $tag
     * @param Instance $instance
     *
     * @return $this
     */
    public function registerInstance( $tag, Instance $instance )
    {
        return $this->manage( $tag, $instance );
    }

    /**
     * @param string $tag The instance to remove from management
     *
     * @return $this
     */
    public function unregisterInstance( $tag )
    {
        return $this->unmanage( $tag );
    }

    /**
     * Get the instance with the corresponding prefix.
     *
     * @param string $tag
     *
     * @throws \LogicException
     *
     * @return Instance
     */
    public function getInstance( $tag )
    {
        return $this->resolve( $tag );
    }

    /**
     * Create a new instance record
     *
     * @param string $instanceName
     * @param array  $options Array of options for creation. Options are:
     *
     *                        owner-id      The id of the instance owner
     *                        cluster-id    The cluster that owns this instance
     *                        trial         If true, the "trial" flagged is set for the instance
     *
     * @return Instance
     * @throws DuplicateInstanceException
     * @throws ProvisioningException
     */
    public function make( $instanceName, $options = [] )
    {
        if ( false === ( $_sanitized = Instance::isNameAvailable( $instanceName ) ) )
        {
            throw new DuplicateInstanceException( 'The instance name "' . $instanceName . '" is not available.' );
        }

        try
        {
            //  Basic checks...
            if ( null === ( $_ownerId = IfSet::get( $options, 'owner-id' ) ) )
            {
                throw new \InvalidArgumentException( 'No "owner-id" given. Cannot create instance.' );
            }

            try
            {
                $_owner = static::_lookupUser( $_ownerId );
            }
            catch ( \Exception $_ex )
            {
                throw new \InvalidArgumentException( 'The "owner-id" specified is invalid.' );
            }

            //  Validate the cluster and pull component ids
            $_guestLocation = IfSet::get( $options, 'guest-location', config( 'dfe.provisioning.default-guest-location' ) );
            $_clusterId = IfSet::get( $options, 'cluster-id', config( 'dfe.provisioning.default-cluster-id' ) );
            $_clusterConfig = $this->_getServersForCluster( $_clusterId );

            //  Write it out
            return \DB::transaction(
                function () use ( $_owner, $_sanitized, $_guestLocation, $_clusterConfig, $options )
                {
                    $_instance = Instance::create(
                        [
                            'user_id'            => $_owner->id,
                            'instance_id_text'   => $_sanitized,
                            'instance_name_text' => $_sanitized,
                            'guest_location_nbr' => $_guestLocation,
                            'cluster_id'         => $_clusterConfig['cluster-id'],
                            'db_server_id'       => $_clusterConfig['db-server-id'],
                            'app_server_id'      => $_clusterConfig['app-server-id'],
                            'web_server_id'      => $_clusterConfig['web-server-id'],
                            'state_nbr'          => ProvisionStates::CREATED,
                            'trial_instance_ind' => IfSet::get( $options, 'trial', false ) ? 1 : 0,
                        ]
                    );

                    if ( !$_instance )
                    {
                        throw new \RuntimeException( 'Instance create fail' );
                    }

                    InstanceGuest::create(
                        [
                            'instance_id'           => $_instance->id,
                            'vendor_id'             => $_guestLocation,
                            'vendor_image_id'       => IfSet::get(
                                $options,
                                'vendor-image-id',
                                config( 'dfe.provisioning.default-vendor-image-id' )
                            ),
                            'vendor_credentials_id' => IfSet::get(
                                $options,
                                'vendor-credentials-id',
                                config( 'dfe.provisioning.default-vendor-credentials-id' )
                            ),
                        ]
                    );

                    return $_instance;
                }
            );
        }
        catch ( \Exception $_ex )
        {
            throw new ProvisioningException( 'Error creating new instance: ' . $_ex->getMessage() );
        }
    }

    /**
     * @param int|string $clusterId
     *
     * @return array
     * @throws ProvisioningException
     */
    protected function _getServersForCluster( $clusterId )
    {
        try
        {
            $_cluster = static::_lookupCluster( $clusterId );
            $_ck = 'instance-manager.cache.clusters.' . $_cluster->cluster_id_text;

            //  Try cache first
            $_servers = \Cache::get( $_ck . '.servers', [] );

            if ( empty( $_servers ) )
            {
                \Cache::put( $_ck . '.servers', $_servers = static::_lookupClusterServers( $_cluster->id ), 5 );
            }
        }
        catch ( ModelNotFoundException $_ex )
        {
            throw new ProvisioningException( 'Cluster "' . $clusterId . '" configuration incomplete or invalid.' );
        }

        if ( null === ( $_server = $this->_locateServerByType( $_servers, ServerTypes::DB ) ) )
        {
            throw new ProvisioningException( 'Cluster "' . $clusterId . '" configuration incomplete or invalid. No database server found.' );
        }

        $_dbId = $_server->id;
        unset( $_server );

        if ( null === ( $_server = $this->_locateServerByType( $_servers, ServerTypes::APP ) ) )
        {
            throw new ProvisioningException( 'Cluster "' . $clusterId . '" configuration incomplete or invalid. No application server found.' );
        }

        $_appId = $_server->id;
        unset( $_server );

        if ( null === ( $_server = $this->_locateServerByType( $_servers, ServerTypes::WEB ) ) )
        {
            throw new ProvisioningException( 'Cluster "' . $clusterId . '" configuration incomplete or invalid. No web server found.' );
        }

        $_webId = $_server->id;
        unset( $_server );

        return [
            'cluster-id'    => $_cluster->id,
            'db-server-id'  => $_dbId,
            'app-server-id' => $_appId,
            'web-server-id' => $_webId,
        ];

    }

    /**
     * @param array|\Illuminate\Support\Collection|Collection $servers
     * @param int                                             $type
     *
     * @return Server|null
     */
    protected function _locateServerByType( $servers, $type )
    {
        if ( !isset( $servers[$type] ) || empty( $servers[$type] ) )
        {
            return null;
        }

        $_ck = 'instance-manager.cache.lru.' . $type;
        $_lastId = \Cache::get( $_ck . '.last-used-id' );
        $_exclude = $_lastId && 1 > count( $servers[$type] ) ? [$_lastId] : [];

        /** @type Server $_server */
        foreach ( $servers[$type] as $_server )
        {
            if ( !in_array( $_server->id, $_exclude ) )
            {
                \Cache::put( $_ck . '.last-used-id', $_server->id, 60 );

                return $_server;
            }
        }

        return null;
    }

    /**
     * @param Instance $instance
     *
     * @return Filesystem
     */
    public function getFilesystem( $instance )
    {
        return $instance->getStorageMount();
    }
}