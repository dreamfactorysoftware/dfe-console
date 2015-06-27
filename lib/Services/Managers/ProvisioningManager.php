<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\Portability;
use DreamFactory\Enterprise\Common\Contracts\PortabilityAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisionerAware;
use DreamFactory\Enterprise\Common\Enums\PortableTypes;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;

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

    /** @inheritdoc */
    public function getPortableServices($name = null)
    {
        $name = GuestLocations::resolve($name ?: $this->getDefaultProvisioner());

        $_services = \Cache::get('dfe.provisioning-manager.portable-services.' . $name);

        if (!empty($_services)) {
            return $_services;
        }

        $_services = [];
        $_list = config('provisioners.hosts.' . $name, []);

        //  Spin through the services
        foreach ($_list as $_key => $_definition) {
            if (PortableTypes::contains($_key)) {
                if (($_service = $this->resolve($name, $_key)) instanceof Portability) {
                    $_services[$_key] = $_service;
                }
            }
        }

        \Cache::put('dfe.provisioning-manager.portable-services.' . $name, $_services, static::CACHE_TTL);

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

        $_namespace = config('provisioners.host.' . $tag . '.namespace');
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
     *
     * @return mixed
     */
    protected function _buildTag($tag, $subkey = null)
    {
        $tag = GuestLocations::resolve($tag ?: $this->getDefaultProvisioner());

        if (null === $subkey) {
            $subkey = PortableTypes::INSTANCE;
        }

        return $tag . '.provides.' . $subkey;
    }
}