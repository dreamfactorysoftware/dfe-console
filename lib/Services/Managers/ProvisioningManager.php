<?php
namespace DreamFactory\Enterprise\Services\Managers;

use DreamFactory\Enterprise\Common\Contracts\ProvisionerAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Managers\BaseManager;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;

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
        return $this->resolve( GuestLocations::resolve( $name ?: $this->getDefaultProvisioner() ) );
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
        return $this->resolveStorage( GuestLocations::resolve( $name ?: $this->getDefaultProvisioner() ) );
    }

    /**
     * Get the default provisioner
     *
     * @return string
     */
    public function getDefaultProvisioner()
    {
        return config( 'provisioners.default' );
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
        $subkey = $subkey ?: 'instance';
        $_key = $tag . '.' . $subkey;

        try
        {
            return parent::resolve( $_key );
        }
        catch ( \InvalidArgumentException $_ex )
        {
        }

        $_class = config( 'provisioners.hosts.' . $_key );

        \Log::debug( 'Configs ' . print_r( $_class, true ) . ' key:' . $_key . ' tag:' . $tag );

        $_provisioner = new $_class( $this->app );
        $this->manage( $_key, $_provisioner );

        return $_provisioner;
    }
}