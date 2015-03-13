<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Managers\BaseManager;

class ProvisioningManager extends BaseManager
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $name
     *
     * @return ResourceProvisioner
     */
    public function provisioner( $name = null )
    {
        return $this->resolve( $name ?: $this->getDefaultProvisioner() );
    }

    /**
     * Returns an instance of the storage provisioner for the specified host
     *
     * @param string $name
     *
     * @return ResourceProvisioner
     */
    public function storageProvisioner( $name = null )
    {
        return $this->resolveStorage( $name ?: $this->getDefaultProvisioner() );
    }

    /**
     * Get the default provisioner
     *
     * @return string
     */
    public function getDefaultProvisioner()
    {
        return $this->app['config']['provisioners.default'];
    }

    /**
     * @param string $tag
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function resolve( $tag )
    {
        return $this->_doResolve( $tag );
    }

    /**
     * @param string $tag
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function resolveStorage( $tag )
    {
        return $this->_doResolve( $tag, 'storage' );
    }

    /**
     * @param string $tag
     * @param string $subkey
     *
     * @return mixed
     */
    protected function _doResolve( $tag, $subkey = null )
    {
        $_key = null !== $subkey ? $tag . '.' . trim( $subkey, '. ' ) : $tag;

        try
        {
            return parent::resolve( $_key );
        }
        catch ( \InvalidArgumentException $_ex )
        {
        }

        $_host = $this->app['config']['provisioners.hosts.' . $tag];
        $_provisioner = new $_host[$subkey ?: 'instance'];

        $this->manage( $_key, $_provisioner );

        return $_provisioner;
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
                $_owner = $this->_findUser( $_ownerId );
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
                $_cluster = $this->_findCluster( $_clusterId );
                $_servers = $this->_clusterServers( $_cluster->id );
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
     * Creates an instance
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return array
     */
    public function up( ProvisioningRequest $request )
    {
        // TODO: Implement up() method.
    }

    /**
     * Destroys an instance
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function down( ProvisioningRequest $request )
    {
        // TODO: Implement down() method.
    }

    /**
     * Replaces an instance
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function replace( ProvisioningRequest $request )
    {
        // TODO: Implement replace() method.
    }

    /**
     * Performs a complete wipe of an instance. The instance is not destroyed, but the database is completely wiped and recreated as if this were a
     * brand new instance. Files in the storage area are NOT touched.
     *
     * @param \DreamFactory\Enterprise\Services\Requests\ProvisioningRequest $request
     *
     * @return mixed
     */
    public function wipe( ProvisioningRequest $request )
    {
        // TODO: Implement wipe() method.
    }
}