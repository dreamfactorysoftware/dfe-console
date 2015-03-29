<?php
namespace Cerberus\Interfaces;

/**
 * ElasticSearchIntervals
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 */
interface ElasticSearchIntervals
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const Year = 'year';
	/**
	 * @var string
	 */
	const Quarter = 'quarter';
	/**
	 * @var string
	 */
	const Month = 'month';
	/**
	 * @var string
	 */
	const Week = 'week';
	/**
	 * @var string
	 */
	const Day = 'day';
	/**
	 * @var string
	 */
	const Hour = 'hour';
	/**
	 * @var string
	 */
	const Minute = 'minute';
}
