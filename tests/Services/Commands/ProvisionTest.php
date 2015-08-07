<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Jobs\ProvisionJob;

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
        $_instanceId = 'wicker';

        $_payload = [
            'instance-id'        => $_instanceId,
            'owner-id'           => 22,
            'owner-type'         => OwnerTypes::USER,
            'guest-location' => GuestLocations::DFE_CLUSTER,
        ];

        /** @var Instance $_instance */
        if (null !== ($_instance = Instance::byNameOrId($_instanceId)->first())) {
            $_instance->delete();
        }

        $_job = new ProvisionJob($_instanceId, $_payload);

        $_result = \Queue::push($_job);
    }
}