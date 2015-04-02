<?php
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Services\Facades\Provision;
use DreamFactory\Enterprise\Services\Provisioners\Rave\Provisioner;
use DreamFactory\Enterprise\Services\Provisioners\Rave\StorageProvisioner;

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
        $_provisioner = Provision::getProvisioner();
        $this->assertTrue( $_provisioner instanceof ResourceProvisioner );

        $_provisioner = Provision::getStorageProvisioner();
        $this->assertTrue( $_provisioner instanceof ResourceProvisioner );

        $_provisioner = Provision::getProvisioner( 'rave' );
        $this->assertTrue( $_provisioner instanceof Provisioner );

        $_provisioner = Provision::getStorageProvisioner( 'rave' );
        $this->assertTrue( $_provisioner instanceof StorageProvisioner );
    }

}
