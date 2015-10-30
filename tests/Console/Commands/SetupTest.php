<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

class SetupTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests update request
     */
    public function testUpdate()
    {
        $_result = \Artisan::call('dfe:setup',
            ['--force' => true, '--admin-password' => 'password', 'admin-email' => 'jerry@dreamfactory.com',]);

        $this->assertEquals(0, $_result);
    }
}
