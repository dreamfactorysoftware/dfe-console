<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Models\Instance;

class ImportTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests provision request
     */
    public function testImport()
    {
        $_instanceId = 'wicker';
        $_snapshotId = '';

        $_instance = Instance::byNameOrId($_instanceId)->firstOrFail();

        $_payload = [
            'owner-id'       => $_instance->user_id,
            'instance-id'    => $_instanceId,
            'snapshot'       => $_snapshotId,
            'snapshot-id'    => true,
            'guest-location' => GuestLocations::DFE_CLUSTER,
        ];

        $_result = \Artisan::call('dfe:import', $_payload);
    }
}