<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

class MigrateInstanceTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests update request
     */
    public function testMigrateInstance()
    {
        $_result = \Artisan::call('dfe:migrate-instance', ['--all']);
    }
}
