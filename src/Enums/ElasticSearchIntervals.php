<?php
namespace DreamFactory\Enterprise\Console\Enums;

use DreamFactory\Library\Utility\Enums\FactoryEnum;

/**
 * ElasticSearch Intervals
 */
class ElasticSearchIntervals extends FactoryEnum
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var string
     */
    const YEAR = 'year';
    /**
     * @var string
     */
    const QUARTER = 'quarter';
    /**
     * @var string
     */
    const MONTH = 'month';
    /**
     * @var string
     */
    const WEEK = 'week';
    /**
     * @var string
     */
    const DAY = 'day';
    /**
     * @var string
     */
    const HOUR = 'hour';
    /**
     * @var string
     */
    const MINUTE = 'minute';
}
