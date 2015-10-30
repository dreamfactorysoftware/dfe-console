<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

class ExportTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function testExport()
    {
        $_instanceId = 'wicker';

        $_result = \Artisan::call('dfe:export', ['instance-id' => $_instanceId]);

        $this->assertNotFalse($_result);
    }
}