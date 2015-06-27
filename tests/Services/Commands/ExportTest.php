<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Services\Commands\ExportJob;

class ExportTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function testExport()
    {
        $_instanceId = 'bender';

        $_job = new ExportJob($_instanceId);

        $_result = \Queue::push($_job);
    }
}