<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\Instance;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $_instanceId = 'jablan';
        $_snapshotId = '20150824141112.jablan';

        try {
            $_instance = Instance::byNameOrId($_instanceId)->firstOrFail();
            throw new \RuntimeException('The instance "' . $_instanceId . '" already exists.');
        } catch (ModelNotFoundException $_ex) {
            //  Good
        }

        $_payload =
            [
                '--instance-id' => $_instanceId,
                '--snapshot'    => $_snapshotId,
                '--owner-id'    => 1,
                '--owner-type'  => OwnerTypes::USER,
                '--snapshot-id' => true,
            ];

        $_result = \Artisan::call('dfe:import', $_payload);
    }
}