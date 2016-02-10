<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

use DreamFactory\Enterprise\Services\Facades\Usage;
use DreamFactory\Enterprise\Services\UsageService;

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
        \Artisan::call('dfe:metrics');
    }
}
