<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

class UpdateTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests update request
     */
    public function testUpdate()
    {
        $_result = \Artisan::call('dfe:update');
    }
}