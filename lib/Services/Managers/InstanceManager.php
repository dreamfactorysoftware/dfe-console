<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\Factory;
use DreamFactory\Enterprise\Common\Enums\ServerTypes;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Common\Traits\StaticComponentLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Enums\ProvisionStates;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\InstanceGuest;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Services\Exceptions\DuplicateInstanceException;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
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

    use StaticComponentLookup;

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
        if (false === ($_sanitized = Instance::isNameAvailable($instanceName))) {
            throw new DuplicateInstanceException('The instance name "' . $instanceName . '" is not available.');
        }

        try {
            //  Basic checks...
            if (null === ($_ownerId = array_get($options, 'owner-id'))) {
                throw new \InvalidArgumentException('No "owner-id" given. Cannot create instance.');
            }

            if (null == ($_ownerType = array_get($options, 'owner-type'))) {
                $_ownerType = OwnerTypes::USER;
            }

            try {
                $_owner = OwnerTypes::getOwner($_ownerId, $_ownerType);
            } catch (ModelNotFoundException $_ex) {
                throw new \InvalidArgumentException('The "owner-id" and/or "owner-type" specified is/are invalid.');
            }

            //  Validate the cluster and pull component ids
            $_guestLocation = array_get($options, 'guest-location', config('dfe.provisioning.default-guest-location'));
            $_clusterId = array_get($options, 'cluster-id', config('dfe.provisioning.default-cluster-id'));
            $_clusterConfig = $this->_getServersForCluster($_clusterId);
            $_ownerId = $_owner->id;

            $_attributes = [
                'user_id'            => $_ownerId,
                'instance_id_text'   => $_sanitized,
                'instance_name_text' => $_sanitized,
                'guest_location_nbr' => $_guestLocation,
                'cluster_id'         => $_clusterConfig['cluster-id'],
                'db_server_id'       => $_clusterConfig['db-server-id'],
                'app_server_id'      => $_clusterConfig['app-server-id'],
                'web_server_id'      => $_clusterConfig['web-server-id'],
                'state_nbr'          => ProvisionStates::CREATED,
                'trial_instance_ind' => !!array_get($options, 'trial', false),
            ];

            $_guestAttributes = [
                'instance_id'           => null,
                'vendor_id'             => $_guestLocation,
                'vendor_image_id'       => array_get(
                    $options,
                    'vendor-image-id',
                    config('dfe.provisioning.default-vendor-image-id')
                ),
                'vendor_credentials_id' => array_get(
                    $options,
                    'vendor-credentials-id',
                    config('dfe.provisioning.default-vendor-credentials-id')
                ),
            ];

            //  Write it out
            return \DB::transaction(
                function () use ($_ownerId, $_attributes, $_guestAttributes) {
                    $_instance = Instance::create($_attributes);
                    $this->debug('created instance row id#' . $_instance->id);

                    $_guestAttributes['instance_id'] = $_instance->id;
                    $_guest = InstanceGuest::create($_guestAttributes);
                    $this->debug('created guest row id#' . $_guest->id);

                    if (!$_instance || !$_guest) {
                        throw new \RuntimeException('Instance creation failed');
                    }

                    return $_instance;
                }
            );
        } catch (\Exception $_ex) {
            throw new ProvisioningException('Error creating new instance: ' . $_ex->getMessage());
        }
    }

    /**
     * @param int|string $clusterId
     *
     * @return array
     * @throws ProvisioningException
     */
    protected function _getServersForCluster($clusterId)
    {
        try {
            $_cluster = static::_lookupCluster($clusterId);
            $_servers = static::_lookupClusterServers($_cluster->id);
            //$this->debug('Servers: ' . print_r($_servers, true));

            $_serverIds = $this->_extractServerIds($_servers);

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
     * @param array  $servers
     * @param string $name
     *
     * @return mixed
     */
    protected function _extractServerIds(array $servers, $name = 'id')
    {
        $_list = ServerTypes::getDefinedConstants(true);
        $_types = array_flip($_list);

        foreach ($_list as $_typeId => $_typeName) {
            $_types[$_typeId] = false;

            if (null === ($_server = array_get($servers, $_typeId))) {
                continue;
            }

            if (null !== ($_id = array_get($_server, '.id'))) {
                $_types[$_typeId] = $_id;
                continue;
            }

            if (null !== ($_ids = array_get($_server, '.ids'))) {
                if (is_array($_ids) && !empty($_ids)) {
                    $_types[$_typeId] = $_ids[0];
                    continue;
                }
            }
        }

        return $_types;
    }

    /**
     * @param array|\Illuminate\Support\Collection|Collection $servers
     * @param int                                             $type
     *
     * @return Server|null
     */
    protected function _locateServerByType($servers, $type)
    {
        if (!isset($servers[$type]) || empty($servers[$type])) {
            return null;
        }

        $_ck = 'instance-manager.cache.lru.' . $type;
        $_lastId = \Cache::get($_ck . '.last-used-id');
        $_exclude = $_lastId && 1 > count($servers[$type]) ? [$_lastId] : [];

        /** @type Server $_server */
        foreach ($servers[$type] as $_server) {
            if (!in_array($_server->id, $_exclude)) {
                \Cache::put($_ck . '.last-used-id', $_server->id, 60);

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
    public function getFilesystem($instance)
    {
        return $instance->getStorageMount();
    }
}