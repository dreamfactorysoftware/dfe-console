<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Enterprise\Services\UsageService;
use DreamFactory\Library\Utility\Json;

class MetricsTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests metrics
     */
    public function testMetrics()
    {
        /** @type UsageService $_service */
        $_service = \App::make(UsageServiceProvider::IOC_NAME);
        $_stats = $_service->gatherStatistics();

        $this->assertNotEmpty($_stats);
    }
}
