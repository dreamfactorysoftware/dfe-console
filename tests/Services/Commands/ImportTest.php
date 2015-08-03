<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

class ImportTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function testExport()
    {
        $_instanceId = 'wicker-2';
        $_ownerId = 22;
        $_snapshot = '20150803014008.wicker';

        $_result = \Artisan::call('dfe:import',
            ['owner-id' => $_ownerId, 'instance-id' => $_instanceId, 'snapshot' => $_snapshot, 'snapshot-id',]);

        $this->assertNotFalse($_result);
    }
}