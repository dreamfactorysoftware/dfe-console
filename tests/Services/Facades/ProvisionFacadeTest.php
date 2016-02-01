<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Facades;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Provisioners\DreamFactory\InstanceProvisioner;
use DreamFactory\Enterprise\Provisioners\DreamFactory\StorageProvisioner;
use DreamFactory\Enterprise\Services\Facades\Provision;

class ProvisionFacadeTest extends \TestCase
{
    /**
     * A basic functional test example.
     *
     * @covers Provision::getFacadeAccessor
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::provisioner
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::getDefaultProvisioner
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::storageProvisioner
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::resolve
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::resolveStorage
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::resolveDatabase
     * @covers DreamFactory\Enterprise\Services\Managers\ProvisioningManager::buildTag
     *
     * @return void
     */
    public function testFacade()
    {
        $_provisioner = Provision::getProvisioner();
        $this->assertTrue($_provisioner instanceof ResourceProvisioner);

        $_provisioner = Provision::getStorageProvisioner();
        $this->assertTrue($_provisioner instanceof ResourceProvisioner);

        $_provisioner = Provision::getProvisioner('dreamfactory');
        $this->assertTrue($_provisioner instanceof InstanceProvisioner);

        $_provisioner = Provision::getStorageProvisioner('dreamfactory');
        $this->assertTrue($_provisioner instanceof StorageProvisioner);
    }
}
