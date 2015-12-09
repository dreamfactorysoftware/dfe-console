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
        //  Up
        $this->assertEquals(0, \Artisan::call('dfe:capsule', ['instance-id' => 'bender']));

        //  Down
        $this->assertEquals(0, \Artisan::call('dfe:capsule', ['instance-id' => 'bender', '--destroy' => true]));
    }
}
