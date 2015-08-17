<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Database\Enums\GuestLocations;
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
        $_instanceId = 'wicker';

        /** @var Instance $_instance */
        if (null !== ($_instance = Instance::byNameOrId($_instanceId)->first())) {
            $_instance->delete();
        }

        $_payload = [
            'instance-id'    => $_instanceId,
            'owner-id'       => 22,
            'guest-location' => GuestLocations::DFE_CLUSTER,
        ];

        \Artisan::call('dfe:provision', $_payload);
    }
}