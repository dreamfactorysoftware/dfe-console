<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\ProvisionerAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Managers\BaseManager;

class ProvisioningManager extends BaseManager implements ProvisionerAware
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $name
     *
     * @return ResourceProvisioner
     */
    public function getProvisioner( $name = null )
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
    public function getStorageProvisioner( $name = null )
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
        $_provisioner = new $_host[$subkey ?: 'instance']( $this->app );

        $this->manage( $_key, $_provisioner );

        return $_provisioner;
    }
}