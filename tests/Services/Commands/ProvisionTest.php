<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\Instance;

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
        $_instanceId = 'wicker2';

        $_payload = [
            'instance-id'    => $_instanceId,
            'owner-id'       => 22,
            'guest-location' => GuestLocations::DFE_CLUSTER,
        ];

        /** @var Instance $_instance */
        if (null !== ($_instance = Instance::byNameOrId($_instanceId)->first())) {
            $_instance->delete();
        }

        $_result = \Artisan::call('dfe:provision', $_payload);

//        $_job = new ProvisionJob($_instanceId, $_payload);
//        $_result = \Queue::push($_job);
    }
}