<?php namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\PortableData;
use DreamFactory\Enterprise\Common\Enums\PortableTypes;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Common\Provisioners\PortableServiceRequest;
use DreamFactory\Enterprise\Common\Provisioners\PortableServiceResponse;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Contracts\PortableProvisionerAware;
use DreamFactory\Enterprise\Services\Contracts\ResourceProvisionerAware;
use DreamFactory\Enterprise\Services\Facades\Snapshot;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use DreamFactory\Enterprise\Services\Jobs\ExportJob;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;
use DreamFactory\Enterprise\Services\Provisioners\ProvisionServiceRequest;
use DreamFactory\Library\Utility\Json;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Symfony\Component\HttpFoundation\Response;

class ProvisioningManager extends BaseManager implements ResourceProvisionerAware, PortableProvisionerAware
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type int The number of minutes to keep things cached
     */
    const CACHE_TTL = 5;

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $name
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function getProvisioner($name = null)
    {
        return $this->resolve($name);
    }

    /**
     * @return array|\DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner[]
     */
    public function getProvisioners()
    {
        $_provisioners = [];

        if (null !== ($_list = config('provisioners.hosts'))) {
            foreach ($_list as $_tag => $_config) {
                if (null !== ($_provisioner = $this->getProvisioner($_tag))) {
                    $_provisioners[$_tag] = $_provisioner;
                }
            }
        }

        return $_provisioners;
    }

    /**
     * Returns an instance of the storage provisioner for the specified host
     *
     * @param string $name
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function getStorageProvisioner($name = null)
    {
        return $this->resolveStorage($name);
    }

    /**
     * Returns an instance of the storage provisioner for the specified host
     *
     * @param string $name
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function getDatabaseProvisioner($name = null)
    {
        return $this->resolveDatabase($name);
    }

    /**
     * Returns an array of the portability providers for this provisioner. If
     * no sub-provisioners are portable, an empty array will be returned.
     *
     * @param string $name The provisioner id. If null, the default provisioner is used.
     *
     * @return array|\DreamFactory\Enterprise\Common\Contracts\PortableData[] An array of portability services keyed by PortableTypes
     */
    public function getPortableServices($name = null)
    {
        $name = GuestLocations::resolve($name ?: $this->getDefaultProvisioner());

        $_services = [];
        $_list = config('provisioners.hosts.' . $name . '.provides', []);

        //  Spin through the services
        foreach ($_list as $_key => $_definition) {
            if (PortableTypes::contains($_key)) {
                if (($_service = $this->resolve($name, $_key)) instanceof PortableData) {
                    $_services[$_key] = $_service;
                }
            }
        }

        //  Return the array
        return $_services;
    }

    /**
     * Get the default provisioner
     *
     * @return string
     */
    public function getDefaultProvisioner()
    {
        return config('provisioners.default');
    }

    /**
     * @param string $tag
     * @param string $subkey
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function resolve($tag, $subkey = null)
    {
        $_key = $this->buildTag($tag, $subkey);

        try {
            return parent::resolve($_key);
        } catch (\InvalidArgumentException $_ex) {
            //  Ignored
        }

        $_namespace = config('provisioners.hosts.' . $tag . '.namespace');
        $_class = ($_namespace ? $_namespace . '\\' : null) . config('provisioners.hosts.' . $_key);

        if (empty($_class)) {
            \Log::notice('Requested provisioner "' . $_key . '" is not valid.');

            return null;
        }

        $this->manage($_key, $_provisioner = new $_class($this->app));

        return $_provisioner;
    }

    /**
     * @param string $tag
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function resolveStorage($tag)
    {
        return $this->resolve($tag, PortableTypes::STORAGE);
    }

    /**
     * @param string $tag
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function resolveDatabase($tag)
    {
        return $this->resolve($tag, PortableTypes::DATABASE);
    }

    /**
     * @param string $tag
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\PortableData|null
     */
    public function resolvePortability($tag)
    {
        //  If db is portable, return it
        $_service = $this->resolveDatabase($tag);

        if ($_service instanceof PortableData) {
            return $_service;
        }

        //  Storage portable?
        $_service = $this->resolveStorage($tag);

        if ($_service instanceof PortableData) {
            return $_service;
        }

        //  Nada
        return null;
    }

    /** @inheritdoc */
    public function provision(ProvisionJob $job)
    {
        return $this->resolve($job->getInstance()->guest_location_nbr)->provision(ProvisionServiceRequest::createProvision($job->getInstance()));
    }

    /** @inheritdoc */
    public function deprovision(DeprovisionJob $job)
    {
        return $this->resolve($job->getInstance()->guest_location_nbr)->deprovision(ProvisionServiceRequest::createDeprovision($job->getInstance()));
    }

    /**
     * Restore portable snapshot data to an instance. If instance does not exist, it is created.
     *
     * @param ImportJob $job
     *
     * @return array an array keyed by provisioner with the results up each ones import
     */
    public function import(ImportJob $job)
    {
        //  Validate instance
        $_instanceId = $job->getInstanceId();

        //  Validate import file
        $_target = $this->validateImportTarget($_snapshot = $job->getTarget());

        try {
            //  Find instance if it exists...
            $_instance = $this->findInstance($_instanceId);
        } catch (ModelNotFoundException $_ex) {
            try {
                $_result = \Artisan::call('dfe:provision',
                    [
                        'owner-id'    => $job->getOwner()->id,
                        'instance-id' => $_instanceId,
                    ]);

                if (0 == $_result) {
                    $_instance = $this->findInstance($_instanceId);
                } else {
                    throw new ModelNotFoundException();
                }
            } catch (ModelNotFoundException $_ex) {
                $this->error('[provisioning:import] import instance "' . $_instanceId . '" provisioning failed');

                throw new \RuntimeException('The instance could not be provisioned.', Response::HTTP_PRECONDITION_FAILED);
            }
        }

        $this->info('[provisioning:import] import instance "' . $_instanceId . '" provisioned.');

        //  Get the services and options
        $_services = $this->getPortableServices(array_get($_options = $job->getOptions(false), 'guest-location'));

        //  Are we done yet?
        if (empty($_services)) {
            $job->setResult(new PortableServiceResponse('Your instance "' .
                $_instanceId .
                '" has been created. However, no portable services are available for instance\'s "guest-location".', Response::HTTP_PARTIAL_CONTENT));

            return true;
        }

        $_imports = [];

        //  Allow each service to import individually, collecting the output
        $_request = PortableServiceRequest::makeImport($_instance,
            $_target,
            array_merge(['snapshot-id' => $_snapshot], $job->getOptions(false)));

        //  Get the manifest
        try {
            $_md = Json::decode($_target->read(config('snapshot.templates.metadata-file-name')));
            $_request->put('original-instance-id', $_md['instance-id']);

            $this->debug('[provisioning:import] uploaded file manifest', $_md);
        } catch (\Exception $_ex) {
            $this->error('[provisioning:import] uploaded file has no recognizable manifest.');

            throw new \RuntimeException('The uploaded file does not appear to be a DFE export and/or snapshot.');
        }

        foreach ($_services as $_type => $_service) {
            try {
                $_imports[$_type] = $_service->import($_request);
                $this->info('[provisioning:import:sub-service] sub-service import: ' . print_r($_imports[$_type], true));
            } catch (\Exception $_ex) {
                $this->error('[provisioning:import:sub-service] exception: ' . $_ex->getMessage());
            }
        }

        $job->setResult(new PortableServiceResponse($_imports));

        return $_imports;
    }

    /**
     * @param \DreamFactory\Enterprise\Services\Jobs\ExportJob $job
     *
     * @return array The list of files in the instance's snapshot mount that were created.
     */
    public function export(ExportJob $job)
    {
        try {
            $_instance = $this->findInstance($job->getInstanceId());
        } catch (ModelNotFoundException $_ex) {
            throw new \RuntimeException('Instance "' . $job->getInstanceId() . '" not found.', Response::HTTP_NOT_FOUND);
        }

        //  Get the portable services of this instance provisioner
        $_services = $this->getPortableServices($_instance->guest_location_nbr);

        //  Allow each service to export individually, collecting the output
        $_exports = [];

        foreach ($_services as $_type => $_service) {
            //@todo I think the following may be replaced with "$_exports[$_type] = $_service->export($job);"
            $_exports[$_type] = $_service->export(PortableServiceRequest::makeExport($_instance, $job->getTarget()));
        }

        return Snapshot::createFromExports($_instance,
            $_exports,
            $job->getTarget() ?: array_get($job->getOptions(), 'target'),
            array_get($job->getOptions(), 'keep-days'));
    }

    /**
     * @param string $tag
     * @param string $subkey
     * @param string $connector The config key connector from $tag to $subkey
     *
     * @return mixed
     */
    protected function buildTag(&$tag, $subkey = null, $connector = '.provides.')
    {
        $tag = trim(GuestLocations::resolve($tag ?: $this->getDefaultProvisioner()));

        if (null === $subkey) {
            $subkey = PortableTypes::INSTANCE;
        }

        return $tag . $connector . $subkey;
    }

    /**
     * Given a snapshot id, return a filesystem containing it
     *
     * @param string $snapshotId
     *
     * @return Filesystem
     */
    protected function validateImportTarget($snapshotId)
    {
        try {
            return $this->findSnapshot($snapshotId)->getMount();
        } catch (ModelNotFoundException $_ex) {
            //  Check if it's a physical local file...
            if (is_file($snapshotId) && file_exists($snapshotId) && is_readable($snapshotId)) {
                return new Filesystem(new ZipArchiveAdapter($snapshotId));
            }

            throw $_ex;
        }
    }
}
