<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\InstanceFactory;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Common\Traits\ComponentLookup;
use DreamFactory\Enterprise\Services\Enums\ProvisionStates;
use DreamFactory\Enterprise\Services\Enums\ServerTypes;
use DreamFactory\Enterprise\Services\Exceptions\DuplicateInstanceException;
use DreamFactory\Enterprise\Services\Utility\RemoteInstance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * InstanceManager
 *
 * @deprecated DO NOT USE
 */
class InstanceManager extends BaseManager implements InstanceFactory
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use ComponentLookup;

    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const CHARACTER_PATTERN = '/[^a-zA-Z0-9]/';
    /**
     * @type string
     */
    const HOST_NAME_PATTERN = "/^([a-zA-Z0-9])+$/";

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
     * @param Instance[] $instances
     */
    public function __construct( array $instances = [] )
    {
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
     * @param array  $options Array of options for creation
     *
     *                        Required Options:
     *                        ================= ==================================================
     *                        owner_id          The id of the owner/user of this instance
     *
     *                        Optional Options
     *                        ================= ==================================================
     *                        cluster_id        The cluster upon which to deploy this instance if DFE-hosted. If not specified, the default cluster
     *                                          id set in the provisioning service's config file will be used.
     *
     *                        instance          An instance model that has already been created
     *                        restart           [REQUIRES "instance" to be specified] If set to TRUE, the instance provided will be restarted.
     *                        guest_location    Where this instance will be provisioned. Defaults to DFE
     *
     *                        tag               If specified, created instance will be registered with this manager as $tag. Name will be used
     *                                          otherwise.
     *
     *                        trial             If set to TRUE, this instance will be marked to be provisioned as a "free trial".
     *
     * @return Instance
     */
    public function make( $instanceName, $options = [] )
    {
        try
        {
            //  Basic checks...
            if ( null === ( $_ownerId = IfSet::get( $options, 'owner-id' ) ) )
            {
                throw new \InvalidArgumentException( 'No owner_id in $options. Cannot create instance.' );
            }

            try
            {
                $_owner = $this->_lookupUser( $_ownerId );
            }
            catch ( \Exception $_ex )
            {
                throw new \InvalidArgumentException( 'The owner_id specified is invalid.' );
            }

            //  Check the instance name
            if ( false === ( $_name = $this->_checkInstance( $instanceName ) ) )
            {
                throw new DuplicateInstanceException( 'The instance "' . $instanceName . '" is already taken.' );
            }

            $_clusterId = IfSet::get( $options, 'cluster-id' ) ?: Config::get( 'dfe.provisioning.default-cluster-id' );

            try
            {
                $_cluster = $this->_lookupCluster( $_clusterId );
                $_servers = $this->_lookupClusterServers( $_cluster->id );
            }
            catch ( ModelNotFoundException $_ex )
            {
                throw new \RuntimeException( 'The specified cluster "' . $_clusterId . '" is not value.' );
            }

            //  Find the database server
            if ( $_servers && $_servers->count() )
            {
                /** @type Server $_server */
                foreach ( $_servers as $_server )
                {
                    if ( $_server->server_type_id == ServerTypes::DB )
                    {
                        $_dbServer = $_server;
                        break;
                    }
                }
            }

            //  Where is this going?
            $_guestLocation = IfSet::get( $options, 'guest-location', Config::get( 'dfe.provisioning.default-guest-location' ) );

            //  If an instance was given, verify it is correct
            if ( null !== ( $_model = IfSet::get( $options, 'instance' ) ) )
            {
                if ( !( $_model instanceof Instance ) && !( $_model instanceof RemoteInstance ) )
                {
                    throw new \InvalidArgumentException( 'The "instance" option must contain an object of type "Instance" or "RemoteInstance".' );
                }
            }

            /** @type Server $_dbServer */
            Log::info( 'BEGIN > Launch Request > ' . $_name . ' on database-server-id ' . $_dbServer->server_id_text . ' of cluster ' . $_clusterId );

            if ( $_model && true === ( $_restart = IfSet::get( $options, 'restart', false ) ) )
            {
                $_model->state_nbr = ProvisionStates::CREATED;
                $_model->provision_ind = 1;
                $_model->deprovision_ind = 0;
                $_model->instance_name_text = $_name;
            }
            else
            {
                $_model = new Instance();
                $_model->user_id = $_owner->id;
                $_model->cluster_id = $_cluster->id;
                $_model->db_server_id = $_dbServer->id;
                $_model->vendor_id = $_guestLocation;
                $_model->vendor_image_id = Config::get( 'dfe.provisioning.default-vendor-image-id' );
                $_model->vendor_credentials_id = 0; //	DreamFactory account
                $_model->platform_state_nbr = 0; // Not Activated
                $_model->ready_state_nbr = 0; // Admin Required
                $_model->state_nbr = ProvisionStates::CREATED;
                $_model->flavor_nbr = Config::get( 'dfe.provisioning.default-vendor-image-flavor' );
                $_model->trial_instance_ind = IfSet::get( $options, 'trial', false ) ? 1 : 0;
                $_model->guest_location_nbr = $_guestLocation;
                $_model->instance_name_text = $_name;
            }

            if ( !$_model->save() )
            {
                throw new \Exception( 'Failed to save instance to database.' );
            }

            //  Register instance with tag if provided, otherwise the name. Access via InstanceManager::instance($tag)...
            $this->registerInstance( IfSet::get( $options, 'tag', $_name ), new RemoteInstance( $_model ) );

            return $_model;
        }
        catch ( \Exception $_ex )
        {
            throw new \RuntimeException( 'Instance creation error: ' . $_ex->getMessage() );
        }
    }

    /**
     * @param string $instanceName
     *
     * @return bool|string
     */
    protected function _checkInstance( $instanceName )
    {
        $_name = Instance::sanitizeName( $instanceName );

        //  Not unique? Bail
        if ( 0 !== Instance::byNameOrId( $_name )->count() )
        {
            return false;
        }

        return $_name;
    }
}