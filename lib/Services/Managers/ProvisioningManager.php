<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\PortableData;
use DreamFactory\Enterprise\Common\Contracts\PortableProvisionerAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisionerAware;
use DreamFactory\Enterprise\Common\Enums\PortableTypes;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Common\Provisioners\PortableServiceRequest;
use DreamFactory\Enterprise\Common\Provisioners\ProvisionServiceRequest;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;
use DreamFactory\Enterprise\Services\Jobs\ExportJob;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;

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
        $_key = $this->_buildTag($tag, $subkey);

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
        return $this->resolve($job->getInstance()->guest_location_nbr)
            ->provision(ProvisionServiceRequest::createProvision($job->getInstance()));
    }

    /** @inheritdoc */
    public function deprovision(DeprovisionJob $job)
    {
        return $this->resolve($job->getInstance()->guest_location_nbr)
            ->deprovision(ProvisionServiceRequest::createDeprovision($job->getInstance()));
    }

    /**
     * @param ImportJob $job
     *
     * @return array
     */
    public function import(ImportJob $job)
    {
        $_instance = $this->_findInstance($job->getInstanceId());
        $_services = $this->getPortableServices($_instance->guest_location_nbr);
        $_imports = [];

        foreach ($_services as $_type => $_service) {
            $_imports[$_type] = $_service->import(PortableServiceRequest::makeImport($_instance, $job->getTarget()));
        }

        return $_imports;
    }

    /**
     * @param \DreamFactory\Enterprise\Services\Jobs\ExportJob $job
     *
     * @return array The list of files in the instance's snapshot mount that were created.
     */
    public function export(ExportJob $job)
    {
        $_instance = $this->_findInstance($job->getInstanceId());
        $_services = $this->getPortableServices($_instance->guest_location_nbr);
        $_exports = [];

        //  Allow each service to export individually, collecting the output
        foreach ($_services as $_type => $_service) {
            $_exports[$_type] = $_service->export(PortableServiceRequest::makeExport($_instance, $job->getTarget()));
        }

        return $_exports;
    }

    /**
     * @param string $tag
     * @param string $subkey
     * @param string $connector The config key connector from $tag to $subkey
     *
     * @return mixed
     */
    protected function _buildTag(&$tag, $subkey = null, $connector = '.provides.')
    {
        $tag = trim(GuestLocations::resolve($tag ?: $this->getDefaultProvisioner()));

        if (null === $subkey) {
            $subkey = PortableTypes::INSTANCE;
        }

        return $tag . $connector . $subkey;
    }
}