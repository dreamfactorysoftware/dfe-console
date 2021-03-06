<?php namespace DreamFactory\Enterprise\Services\Managers;

use DB;
use DreamFactory\Enterprise\Common\Contracts\Factory;
use DreamFactory\Enterprise\Common\Enums\ServerTypes;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Enums\ProvisionStates;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\InstanceGuest;
use DreamFactory\Enterprise\Services\Exceptions\DuplicateInstanceException;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Creates and manages instances
 */
class InstanceManager extends BaseManager implements Factory
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Constructor
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param Instance[]                                   $instances
     */
    public function __construct($app, array $instances = [])
    {
        parent::__construct($app);

        !empty($instances) && $this->registerInstances($instances);
    }

    /**
     * Register instances
     *
     * @param Instance[] $instances [:tag => instance,]
     *
     * @return $this
     */
    public function registerInstances(array $instances)
    {
        foreach ($instances as $_tag => $_instance) {
            $this->registerInstance($_tag, $_instance);
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
    public function registerInstance($tag, Instance $instance)
    {
        return $this->manage($tag, $instance);
    }

    /**
     * @param string $tag The instance to remove from management
     *
     * @return $this
     */
    public function unregisterInstance($tag)
    {
        return $this->unmanage($tag);
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
    public function getInstance($tag)
    {
        return $this->resolve($tag);
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
    public function make($instanceName, $options = [])
    {
        try {
            //  Basic checks...
            if (null === ($_ownerId = array_get($options, 'owner-id'))) {
                throw new InvalidArgumentException('No "owner-id" given. Cannot create instance.');
            }

            if (null === ($_ownerType = array_get($options, 'owner-type'))) {
                $_ownerType = OwnerTypes::USER;
            }

            try {
                $_owner = OwnerTypes::getOwner($_ownerId, $_ownerType);
            } catch (ModelNotFoundException $_ex) {
                throw new InvalidArgumentException('The "owner-id" and/or "owner-type" specified is/are invalid.');
            }

            //logger('owner validated: ' . $_owner->id . ($_owner->admin_ind ? ' (admin)' : ' (non-admin)'));

            if (false === ($_sanitized = Instance::isNameAvailable($instanceName, $_owner->admin_ind))) {
                throw new DuplicateInstanceException('The instance name "' . $instanceName . '" is not available.');
            }

            //  Get the proper location
            $_guestLocation = array_get($options, 'guest-location', config('provisioning.default-guest-location'));

            //  Validate the cluster and pull component ids
            $_clusterId = array_get($options, 'cluster-id', config('provisioning.default-cluster-id'));
            $_clusterConfig = $this->getClusterServerIds($_clusterId);
            $_ownerId = $_owner->id;

            $_attributes = [
                'user_id'            => (int)$_ownerId,
                'instance_id_text'   => $_sanitized,
                'instance_name_text' => $_sanitized,
                'guest_location_nbr' => $_guestLocation,
                'cluster_id'         => (int)$_clusterConfig['cluster-id'],
                'db_server_id'       => (int)$_clusterConfig['db-server-id'],
                'app_server_id'      => (int)$_clusterConfig['app-server-id'],
                'web_server_id'      => (int)$_clusterConfig['web-server-id'],
                'state_nbr'          => ProvisionStates::CREATED,
            ];

            $_guestAttributes = [
                'instance_id'           => null,
                'vendor_id'             => $_guestLocation,
                'vendor_image_id'       => array_get($options,
                    'vendor-image-id',
                    config('provisioning.default-vendor-image-id')),
                'vendor_credentials_id' => array_get($options,
                    'vendor-credentials-id',
                    config('provisioning.default-vendor-credentials-id')),
            ];

            //  Write it out
            return DB::transaction(function() use ($_ownerId, $_attributes, $_guestAttributes) {
                $_instance = Instance::create($_attributes);
                logger('created instance row id#' . $_instance->id);

                $_guestAttributes['instance_id'] = $_instance->id;
                $_guest = InstanceGuest::create($_guestAttributes);
                //logger('created guest row id#' . $_guest->id);

                if (!$_instance || !$_guest) {
                    throw new RuntimeException('Instance creation failed');
                }

                return $_instance;
            });
        } catch (Exception $_ex) {
            throw new ProvisioningException('Error creating new instance: ' . $_ex->getMessage());
        }
    }

    /**
     * @param int|string $clusterId
     *
     * @return array
     * @throws ProvisioningException
     */
    protected function getClusterServerIds($clusterId)
    {
        try {
            $_cluster = $this->findCluster($clusterId);
            $_servers = $this->findClusterServers($_cluster);
            $_serverIds = $this->extractServerIds($_servers);

            return [
                'cluster-id'    => $_cluster->id,
                'db-server-id'  => $_serverIds[ServerTypes::DB],
                'app-server-id' => $_serverIds[ServerTypes::APP],
                'web-server-id' => $_serverIds[ServerTypes::WEB],
            ];
        } catch (ModelNotFoundException $_ex) {
            throw new ProvisioningException('Cluster "' . $clusterId . '" configuration incomplete or invalid.');
        }
    }

    /**
     * @param array  $servers A list of servers indexed by ServerType
     * @param string $name
     *
     * @see ServerTypes
     * @return mixed
     */
    protected function extractServerIds(array $servers, $name = 'id')
    {
        $_ids = [];

        //  Default the result
        foreach (ServerTypes::all() as $_constant => $_value) {
            $_ids[$_value] = null;
        }

        //  Spin through the assigned servers
        foreach ($servers as $_type => $_pool) {
            //  Right now, we're only using the first entry in the pool
            foreach ($_pool as $_server) {
                $_ids[$_type] = data_get($_server, $name);
                break;
            }
        }

        return $_ids;
    }
}
