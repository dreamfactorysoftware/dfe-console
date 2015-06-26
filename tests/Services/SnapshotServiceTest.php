<?php namespace DreamFactory\Enterprise\Console\Tests\Services;

use DreamFactory\Enterprise\Services\Facades\Snapshot;

class SnapshotServiceTest extends \TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testCreate()
    {
        $_snapshot = Snapshot::create('bender');
    }
}
