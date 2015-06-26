<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\Portability;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisionerAware;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;

class ProvisioningManager extends BaseManager implements ResourceProvisionerAware
{
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

        if (null !== ($_list = config('dfe.provisioners.hosts'))) {
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
     * Returns an instance of the portability provisioner for the specified host, if any
     *
     * @param string $name
     *
     * @return Portability
     */
    public function getPortabilityProvider($name = null)
    {
        return $this->resolvePortability($name);
    }

    /**
     * Get the default provisioner
     *
     * @return string
     */
    public function getDefaultProvisioner()
    {
        return config('dfe.provisioners.default');
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
        }

        $_class = config('dfe.provisioners.hosts.' . $_key);

        if (empty($_class)) {
            \Log::notice('Requested provisioner "' . $_key . '" is not valid.');

            return null;
        }

        $_provisioner = new $_class($this->app);
        $this->manage($_key, $_provisioner);

        return $_provisioner;
    }

    /**
     * @param string $tag
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function resolveStorage($tag)
    {
        return $this->resolve($tag, 'storage');
    }

    /**
     * @param string $tag
     *
     * @return \DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner
     */
    public function resolveDatabase($tag)
    {
        return $this->resolve($tag, 'db');
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
            $subkey = 'instance';
        }

        return $tag . '.' . $subkey;
    }
}