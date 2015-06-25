<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Enums\AppKeyClasses;
use DreamFactory\Enterprise\Common\Enums\ManifestTypes;
use DreamFactory\Enterprise\Common\Support\Metadata;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Enums\ProvisionStates;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Contracts\HasOfferings;
use DreamFactory\Enterprise\Services\Contracts\Offering;
use DreamFactory\Enterprise\Services\Contracts\OfferingProvisioner;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Exceptions\SchemaExistsException;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\BaseProvisioner;
use DreamFactory\Enterprise\Services\Provisioners\ProvisionerOffering;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Contracts\Filesystem\Filesystem;

class Provisioner extends BaseProvisioner implements HasOfferings, OfferingProvisioner
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array My offerings
     */
    protected $_offerings = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function boot()
    {
        parent::boot();

        //  Have we read these yet?
        if (false === $this->_offerings) {
            $this->_offerings = [];
            $_list = config('dfe.provisioners.hosts.rave.offerings', []);

            if (is_array($_list) && !empty($_list)) {
                foreach ($_list as $_key => $_value) {
                    if (!empty($_key)) {
                        $_offer = new ProvisionerOffering($_key, $_value);
                        $this->_offerings[$_key] = $_offer->toArray();
                    }
                }
            }
            //\Log::debug( '     * Loaded ' . count( $this->_offerings ) . ' offering(s) for provisioner "rave".' );
        }
    }

    /**
     * Get the current status of a provisioning request
     *
     * @param Instance $instance
     *
     * @return array
     */
    public function status(Instance $instance)
    {
        /** @var Instance $_instance */
        if (null === ($_instance = Instance::find($instance->id))) {
            return ['success' => false, 'error' => ['code' => 404, 'message' => 'Instance not found.']];
        }

        return [
            'success'     => true,
            'status'      => $_instance->state_nbr,
            'status_text' => ProvisionStates::prettyNameOf($_instance->state_nbr),
        ];
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return array
     */
    protected function _doProvision($request, $options = [])
    {
        $_output = [];
        $_result = false;
        $_instance = $request->getInstance();

        //	Update the current instance state
        $_instance->updateState(ProvisionStates::PROVISIONING);

        try {
            //  Provision storage and fill in the request
            $this->_provisionStorage($request, $options);

            //  And the instance
            $_result = $this->_provisionInstance($request, $options);

            return ['success' => true, 'instance' => $_instance->toArray(), 'log' => $_output, 'result' => $_result];
        } catch (\Exception $_ex) {
            $this->error('     * exception: ' . $_ex->getMessage());

            $_instance->updateState(ProvisionStates::PROVISIONING_ERROR);

            //  Force-kill anything we made before blowing up
            $request->setForced(true);

            $this->_deprovisionStorage($request);

            if (!$this->_deprovisionInstance($request, ['keep-database' => ($_ex instanceof SchemaExistsException)])) {
                $this->error('Unable to remove instance "' . $_instance->instance_id_text . '" after failed provision.');
            }

            return ['success' => false, 'instance' => false, 'log' => $_output, 'result' => $_result];
        }
    }

    /**
     * @param \DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest $request
     * @param array                                                              $options
     *
     * @return array
     */
    protected function _doDeprovision($request, $options = [])
    {
        $_output = [];
        $_result = false;
        $_instance = $request->getInstance();

        //	Update the current instance state
        $_instance->updateState(ProvisionStates::DEPROVISIONING);

        try {
            $_result = $this->_deprovisionInstance($request, $options);

            return ['success' => true, 'instance' => $_instance->toArray(), 'log' => $_output, 'result' => $_result];
        } catch (\Exception $_ex) {
            $_instance->updateState(ProvisionStates::DEPROVISIONING_ERROR);

            return ['success' => false, 'instance' => false, 'log' => $_output, 'result' => $_result];
        }
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return Filesystem
     */
    protected function _provisionStorage($request, $options = [])
    {
        \Log::debug('  * rave: provision storage - begin');

        //  Use requested file system if one...
        $_filesystem = $request->getStorage();

        //  Do it!
        $request->setStorageProvisioner($_provisioner = Provision::resolveStorage($request->getInstance()->guest_location_nbr));

        $_provisioner->provision($request);

        \Log::debug('  * rave: provision storage - complete');

        return $_filesystem;
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return bool
     */
    protected function _deprovisionStorage($request, $options = [])
    {
        \Log::debug('  * rave: deprovision storage');

        //  Use requested file system if one...
        $_filesystem = $request->getStorage();

        //  Do it!
        Provision::resolveStorage($request->getInstance()->guest_location_nbr)->deprovision($request);

        \Log::debug('  * rave: deprovision storage - complete');

        return $_filesystem;
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return array
     * @throws ProvisioningException
     */
    protected function _provisionInstance($request, $options = [])
    {
        $_storagePath = null;

        //	Pull the request apart
        $_instance = $request->getInstance();
        $_name = $_instance->instance_name_text;
        $_storageKey = $_instance->storage_id_text;

        \Log::debug('  * rave: provision instance "' . $_name . '" - begin');

        $_storageProvisioner = $request->getStorageProvisioner();
        $_privatePath = $_storageProvisioner->getPrivatePath();
        $_ownerPrivatePath = $_storageProvisioner->getOwnerPrivatePath();

        $_dbConfigFile = $_ownerPrivatePath . DIRECTORY_SEPARATOR . $_name . '.database.config.php';

        //	1. Provision the database
        $_dbService = Provision::getDatabaseProvisioner($_instance->guest_location_nbr);
        $_dbConfig = $_dbService->provision($request);

        $_dbUser = $_dbConfig['username'];
        $_dbPassword = $_dbConfig['password'];
        $_dbName = $_dbConfig['database'];

        //  2. Update the instance...
        $_host = $_name . '.' . config('dfe.provisioning.default-dns-zone') . '.' . config('dfe.provisioning.default-dns-domain');

        //	Update instance with new provision info
        try {
            $_instance->fill([
                'guest_location_nbr' => GuestLocations::DFE_CLUSTER,
                'instance_id_text'   => $_name,
                'instance_name_text' => $_name,
                'db_host_text'       => $_dbConfig['host'],
                'db_port_nbr'        => $_dbConfig['port'],
                'db_name_text'       => $_dbName,
                'db_user_text'       => $_dbUser,
                'db_password_text'   => $_dbPassword,
                'ready_state_nbr'    => 0, //   Admin Required
                'state_nbr'          => ProvisionStates::PROVISIONED,
                'platform_state_nbr' => 0, //   Not Activated
                'start_date'         => date('c'),
                'end_date'           => null,
                'terminate_date'     => null,
                'provision_ind'      => 1,
                'deprovision_ind'    => 0,
            ]);

            /**
             * Generate an app key for the instance
             */
            $_appKey = AppKey::create([
                'key_class_text' => AppKeyClasses::INSTANCE,
                'owner_id'       => $_instance->id,
                'owner_type_nbr' => OwnerTypes::INSTANCE,
                'server_secret'  => config('dfe.security.console-api-key'),
            ]);

            /** @type Cluster $_cluster */
            $_cluster = Cluster::findOrFail($_instance->cluster_id, ['cluster_id_text']);

            //  Collect metadata
            $_md = new Metadata($_instance->getOwnerPrivateStorageMount(),
                [],
                $_instance->instance_name_text . '.json');

            $_md->set('db', [$_name => $_dbConfig])
                ->set('paths',
                    [
                        'private-path'       => $_privatePath,
                        'owner-private-path' => $_ownerPrivatePath,
                        'snapshot-path' => $_ownerPrivatePath . DIRECTORY_SEPARATOR . config('dfe.provisioning.snapshot-path-name',
                                ConsoleDefaults::SNAPSHOT_PATH_NAME),
                    ])
                ->set('env',
                    [
                        'cluster-id'       => $_cluster->cluster_id_text,
                        'default-domain'   => config('dfe.provisioning.default-domain'),
                        'signature-method' => config('dfe.signature-method'),
                        'storage-root'     => config('dfe.provisioning.storage-root'),
                        'console-api-url'  => config('dfe.security.console-api-url'),
                        'console-api-key'  => config('dfe.security.console-api-key'),
                        'client-id'        => $_appKey->client_id,
                        'client-secret'    => $_appKey->client_secret,
                    ]);

            //  Merge in the metadata
            $_instanceData = $_instance->instance_data_text;
            $_instanceData[ManifestTypes::METADATA] = $_md->toArray();
            $_instance->instance_data_text = $_instanceData;

            \DB::transaction(function () use ($_instance, $_host) {
                /**
                 * Add guest data if there is a guest record
                 */
                $_instance->guest && $_instance->guest->fill([
                    'base_image_text'   => config('dfe.provisioning.base-image',
                        ConsoleDefaults::DFE_CLUSTER_BASE_IMAGE),
                    'vendor_state_nbr'  => ProvisionStates::PROVISIONED,
                    'vendor_state_text' => 'running',
                    'public_host_text'  => $_host,
                ])->save();

                //  Save the instance
                $_instance->save();
            });

            //  Try 'n Save the metadata
            try {
                $_md->write();
            } catch (\Exception $_ex) {
                \Log::error('Exception saving instance metadata: ' . $_ex->getMessage());
            }
            //\Log::debug( '    * rave: instance update - complete' );
        } catch (\Exception $_ex) {
            throw new \RuntimeException('Error updating instance data: ' . $_ex->getMessage());
        }

        //  Fire off a "launch" event...
        //\Log::debug( '    * rave: fire "dfe.launch" event' );
        \Log::info('  * rave: provision "' . $_name . '" - complete');

        \Event::fire('dfe.launch', [$this, $request, $_md]);

        return [
            'host'                => $_host,
            'storage_key'         => $_storageKey,
            'blob_path'           => $_storagePath,
            'storage_path'        => $_storagePath,
            'private_path'        => $_privatePath,
            'owner_private_path'  => $_ownerPrivatePath,
            'snapshot_path'       => $_ownerPrivatePath . DIRECTORY_SEPARATOR . config('dfe.provisioning.snapshot-path-name',
                    ConsoleDefaults::SNAPSHOT_PATH_NAME),
            'db_host'             => $_dbConfig['host'],
            'db_port'             => $_dbConfig['port'],
            'db_name'             => $_dbName,
            'db_user'             => $_dbUser,
            'db_password'         => $_dbPassword,
            'db_config_file_name' => $_dbConfigFile,
            'cluster'             => $_instance->cluster_id,
            'metadata'            => $_md,
        ];
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options ['keep-database'=>true|false]
     *
     * @return bool
     * @throws ProvisioningException
     */
    protected function _deprovisionInstance($request, $options = [])
    {
        $_instance = $request->getInstance();
        $_keepDatabase = IfSet::get($options, 'keep-database', false);

        if ($_keepDatabase) {
            $this->notice('    * rave: not removing existing schema.');
        } else {
            //	Deprovision the database
            $_dbService = Provision::getDatabaseProvisioner($_instance->guest_location_nbr);

            if (false === ($_dbConfig = $_dbService->deprovision($request))) {
                throw new ProvisioningException('Failed to deprovision database. Check logs for error.');
            }
        }

        if (!$_instance->delete()) {
            throw new \RuntimeException('Instance row deletion failed.');
        }

        \Log::debug('    * rave: instance deleted');

        //  Fire off a "shutdown" event...
        \Event::fire('dfe.shutdown', [$this, $request]);
        \Log::debug('    * rave: event "dfe.shutdown" fired');

        return true;
    }

    /**
     * @return Offering[]
     */
    public function getOfferings()
    {
        return $this->_offerings;
    }

    /**
     * @return string The id of this provisioner
     */
    public function getProvisionerId()
    {
        return 'rave';
    }
}