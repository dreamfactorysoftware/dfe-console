<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Services\Facades\Usage;
use DreamFactory\Enterprise\Services\UsageService;

class MetricsTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests metrics
     *
     * @covers UsageService::gatherStatistics()
     */
    public function testMetrics()
    {
        /** @type UsageService $_service */
        $_service = Usage::service();
        $_stats = $_service->gatherStatistics();

        $this->assertNotEmpty($_stats);
    }
}
