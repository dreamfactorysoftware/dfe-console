<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\Portability;
use DreamFactory\Enterprise\Common\Contracts\PortabilityAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisionerAware;
use DreamFactory\Enterprise\Common\Enums\PortableTypes;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Jobs\ExportJob;
use DreamFactory\Enterprise\Services\Jobs\ImportJob;
use DreamFactory\Enterprise\Services\Provisioners\PortabilityRequest;

class ProvisioningManager extends BaseManager implements ResourceProvisionerAware, PortabilityAware
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
     * @return ResourceProvisioner
     */
    public function getProvisioner($name = null)
    {
        return $this->resolve($name);
    }

    /**
     * @return ResourceProvisioner[]
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
     * @return ResourceProvisioner
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
     * @return ResourceProvisioner
     */
    public function getDatabaseProvisioner($name = null)
    {
        return $this->resolveDatabase($name);
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
            $_request = PortabilityRequest::createImport($_instance, $job);
            $_imports[$_type] = $_service->import($_request, $_request->getFrom());
        }

        return $_imports;
    }

    /**
     * @param ExportJob $job
     *
     * @return array
     */
    public function export(ExportJob $job)
    {
        $_instance = $this->_findInstance($job->getInstanceId());
        $_services = $this->getPortableServices($_instance->guest_location_nbr);
        $_exports = [];

        foreach ($_services as $_type => $_service) {
            $_request = PortabilityRequest::createExport($_instance, $job);
            $_exports[$_type] = $_service->export($_request, $_request->getTo());
        }

        return $_exports;
    }

    /**
     * Returns an array of the portability providers for this provisioner. If
     * no sub-provisioners are portable, an empty array will be returned.
     *
     * @param string $name The provisioner id. If null, the default provisioner is used.
     *
     * @return Portability[] An array of portability services keyed by PortableTypes
     */
    public function getPortableServices($name = null)
    {
        $name = GuestLocations::resolve($name ?: $this->getDefaultProvisioner());

        $_services = [];
        $_list = config('provisioners.hosts.' . $name . '.provides', []);

        //  Spin through the services
        foreach ($_list as $_key => $_definition) {
            if (PortableTypes::contains($_key)) {
                if (($_service = $this->resolve($name, $_key)) instanceof Portability) {
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
     * @return ResourceProvisioner
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
     * @return Portability|null
     */
    public function resolvePortability($tag)
    {
        //  If db is portable, return it
        $_service = $this->resolveDatabase($tag);

        if ($_service instanceof Portability) {
            return $_service;
        }

        //  Storage portable?
        $_service = $this->resolveStorage($tag);

        if ($_service instanceof Portability) {
            return $_service;
        }

        //  Nada
        return null;
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