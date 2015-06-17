<?php
namespace DreamFactory\Enterprise\Console\Facades;

use DreamFactory\Enterprise\Console\Enums\ElkIntervals;
use DreamFactory\Enterprise\Console\Providers\ElkServiceProvider;
use Elastica\Client;
use Elastica\ResultSet;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ResultSet callOverTime(string $facility, string $interval = ElkIntervals::DAY, int $size = 30, int
 *         $from = 0, string $term = null)
 * @method static bool|array globalStats(int $from = 0, int $size = 1)
 * @method static array allStats(int $from = 0, int $size = 1)
 * @method static ResultSet termQuery(string $term, string $value, int $size = 30)
 * @method static Client getClient();
 */
class Elk extends Facade
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    /** @noinspection PhpMissingParentCallCommonInspection */
    protected static function getFacadeAccessor()
    {
        return ElkServiceProvider::IOC_NAME;
    }
}