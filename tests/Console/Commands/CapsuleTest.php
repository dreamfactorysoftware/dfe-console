<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

class CapsuleTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests dfe:capsule
     */
    public function testCapsule()
    {
        $_result = \Artisan::call('dfe:capsule', ['instance-id' => 'bender']);
    }
}
