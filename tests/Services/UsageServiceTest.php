<?php namespace DreamFactory\Enterprise\Console\Tests\Services;

use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Enterprise\Services\UsageService;

class UsageServiceTest extends \TestCase
{
    /**
     * A basic functional test example.
     */
    public function testHarvest()
    {
        /** @type UsageService $_service */
        $_service = \App::make(UsageServiceProvider::IOC_NAME);
        $_stats = $_service->gatherStatistics();
    }
}
