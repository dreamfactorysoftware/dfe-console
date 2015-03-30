<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\StaticFactory;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Common\Traits\InstanceValidation;
use DreamFactory\Enterprise\Common\Traits\StaticComponentLookup;
use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Enums\ServerTypes;
use DreamFactory\Enterprise\Services\Exceptions\DuplicateInstanceException;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Creates and manages instances
 */
class InstanceManager extends BaseManager implements StaticFactory
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use StaticComponentLookup, InstanceValidation;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The DFE app server to talk to
     */
    protected $_dfeEndpoint = 'http://localhost/api/v1';

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
     * @throws \DreamFactory\Enterprise\Services\Exceptions\DuplicateInstanceException
     */
    public static function make( $instanceName, $options = [] )
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

            /*------------------------------------------------------------------------------*/
            /* Validate the cluster and pull components                                     */
            /*------------------------------------------------------------------------------*/

            $_guestLocation = IfSet::get( $options, 'guest-location', config( 'dfe.provisioning.default-guest-location' ) );
            $_clusterId = IfSet::get( $options, 'cluster-id', config( 'dfe.provisioning.default-cluster-id' ) );

            try
            {
                $_cluster = static::_lookupCluster( $_clusterId );
                $_cachePrefix = __CLASS__ . '.clusters.' . $_cluster->cluster_id_text;

                //  Try cache first
                $_servers = \Cache::get( $_cachePrefix . '.servers', [] ) ?: static::_lookupClusterServers( $_cluster->id );
            }
            catch ( ModelNotFoundException $_ex )
            {
                throw new \RuntimeException( 'The specified cluster "' . $_clusterId . '" is not value.' );
            }

            //  Find the database server
            $_dbServer = \Cache::get( $_cachePrefix . '.lru.db-server-id' );

            if ( !empty( $_servers ) )
            {
                //  Cache it
                \Cache::put( $_cachePrefix . '.servers', $_servers, 60 );

                //@todo re-examine round-robin approach to assigning cluster database servers
                $_ignored = $_dbServer && 1 > count( $_servers[ServerTypes::DB] ) ? [$_dbServer] : [];

                /** @type Server $_server */
                foreach ( $_servers[ServerTypes::DB] as $_server )
                {
                    if ( !in_array( $_server->server_id_text, $_ignored ) )
                    {
                        $_dbServer = $_server;
                        \Cache::put( $_cachePrefix . '.lru.db-server', $_dbServer, 60 );
                        break;
                    }
                }
            }

            //  Misconfigured cluster?
            if ( empty( $_dbServer ) )
            {
                throw new \RuntimeException( 'No database server is configured for cluster "' . $_cluster->cluster_id_text . '".' );
            }

            //  Write it out
            return Instance::create(
                [
                    'user_id'            => $_owner->id,
                    'cluster_id'         => $_cluster->id,
                    'db_server_id'       => $_dbServer->id,
                    'vendor_id'          => $_guestLocation,
                    'vendor_image_id'    => IfSet::get( $options, 'vendor-image-id', 4764 ),
                    'state_nbr'          => ProvisionStates::CREATED,
                    'trial_instance_ind' => IfSet::get( $options, 'trial', false ) ? 1 : 0,
                    'guest_location_nbr' => $_guestLocation,
                    'instance_name_text' => $_sanitized,
                    'instance_id_text'   => $_sanitized,
                ]
            );
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Instance creation error: ' . $_ex->getMessage() );
        }
    }

    /**
     * Retrieves an instance's metadata
     *
     * @param Instance $instance
     *
     * @return array
     */
    public function getInstanceMetadata( Instance $instance )
    {
        if ( !$instance->user )
        {
            throw new \RuntimeException( 'The user for instance "' . $instance->instance_id_text . '" was not found.' );
        }

        $_response = [
            'instance-id'         => $instance->id,
            'cluster-id'          => $instance->cluster_id,
            'db-server-id'        => $instance->db_server_id,
            'app-server-id'       => $instance->app_server_id,
            'web-server-id'       => $instance->web_server_id,
            'owner-id'            => $instance->user_id,
            'owner-email-address' => $instance->user->email_addr_text,
        ];

        return $_response;
    }

}