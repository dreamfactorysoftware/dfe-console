<?php
namespace DreamFactory\Enterprise\Services\Tests\Commands;

use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use DreamFactory\Enterprise\Services\Controllers\InstanceController;
use DreamFactory\Enterprise\Services\Provisioners\DreamFactory\InstanceProvisioner;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

class ProvisionTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests provision request
     */
    public function testProvision()
    {
        $_manager = new InstanceController();
        $_manager->getEnvironment();

        $_instanceId = 'dfe-test-dsp';

        if ( null !== ( $_instance = Instance::byNameOrId( $_instanceId )->first() ) )
        {
            $_instance->delete();
        }

        $_command = new ProvisionJob( $_instanceId, ['owner_id' => 1, 'guest_location' => 2, 'tag' => 'test'] );

        /** @type InstanceProvisioner $_service */
        $_service = app( 'provisioner.dfe' );
        $this->assertNotNull( $_service );

        $_request = new ProvisioningRequest();

        $_request
            ->setInstance( $_command->getInstance() )
            ->setDeprovisioning( false )
            ->setInstanceId( $_instanceId );

        $_service->provision( $_request );
    }
}