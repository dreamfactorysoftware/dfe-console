<?php
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\RaveProvisioner;
use DreamFactory\Enterprise\Services\Provisioners\RaveStorageProvisioner;

class ProvisionTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @covers Provision::getFacadeAccessor
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::provisioner
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::getDefaultProvisioner
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::storageProvisioner
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::_doResolve
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::resolve
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::resolveStorage
     *
     * @return void
     */
    public function testFacade()
    {
        $_provisioner = Provision::provisioner();
        $this->assertTrue( $_provisioner instanceof ResourceProvisioner );

        $_provisioner = Provision::storageProvisioner();
        $this->assertTrue( $_provisioner instanceof ResourceProvisioner );

        $_provisioner = Provision::provisioner( 'rave' );
        $this->assertTrue( $_provisioner instanceof RaveProvisioner );

        $_provisioner = Provision::storageProvisioner( 'rave' );
        $this->assertTrue( $_provisioner instanceof RaveStorageProvisioner );

        $_provisioner = Provision::storageProvisioner( 'rave' );
        $this->assertTrue( $_provisioner instanceof RaveStorageProvisioner );
    }

}
