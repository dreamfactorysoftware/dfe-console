<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

class BlueprintTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests update request
     */
    public function testBlueprint()
    {
        $_result = \Artisan::call('dfe:blueprint', ['instance-id' => 'bender']);
    }
}
