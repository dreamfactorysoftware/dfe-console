<?php
namespace Cerberus\Services;

use Cerberus\Interfaces\ElasticSearchIntervals;
use DreamFactory\Services\DreamService;
use Elastica\Client;
use Elastica\Facet;
use Elastica\Filter\Bool;
use Elastica\Filter\Prefix;
use Elastica\Query;
use Elastica\Search;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\Log;

/**
 * GraylogData.php.php
 *
 * @copyright       Copyright (c) 2013 DreamFactory Software, Inc.
 * @link            DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @package         cerberus
 * @filesource
 */
class GraylogData extends DreamService implements ElasticSearchIntervals
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const GRAYLOG_INDEX = 'graylog2_0';

	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * @var \Elastica\Client
	 */
	protected $_client = null;

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param ConsumerLike $consumer
	 * @param array        $settings
	 *
	 * @throws \RuntimeException
	 */
	public function __construct( ConsumerLike $consumer, $settings = array() )
	{
		parent::__construct( $consumer, $settings );

		$_config = require_once \Kisma::get( 'app.config_path' ) . '/elasticsearch.config.php';

		if ( empty( $_config ) )
		{
			throw new \RuntimeException( 'No configuration file found for elasticsearch.' );
		}

		$this->_client = new Client( $_config );
	}

	/**
	 * @param string $facility
	 * @param string $interval
	 * @param int    $size
	 * @param int    $from
	 * @param string $term
	 *
	 * @throws \InvalidArgumentException
	 * @return \Elastica\ResultSet
	 */
	public function callOverTime( $facility, $interval = self::Day, $size = 30, $from = 0, $term = null )
	{
		if ( !\Cerberus\Enums\ElasticSearchIntervals::contains( $interval ) )
		{
			throw new \InvalidArgumentException( 'The interval of "' . $interval . '" is not valid.' );
		}

		if ( empty( $term ) )
		{
			$_query = array(
				'query'  => array(
					array(
						'match_all' => array(),
					),
				),
				'size'   => $size,
				'from'   => $from,
				'facets' => array(
					'facilities'   => array(
						'terms' => array(
							'field' => 'message.facility',
							'size'  => 10,
						)
					),
					'published_on' => array(
						'date_histogram' => array(
							'field'    => 'histogram_time',
							'interval' => $interval,
						)
					)
				)
			);

			if ( !empty( $facility ) )
			{
				unset( $_query['query'] );

				$_key = 'wildcard'; //( false !== strpos( $facility, '*' ) ? 'wildcard' : 'term' );

				$_query['query'] = array(
					'bool' => array(
						'must' => array(
							$_key => array(
								'message.facility' => $facility,
							),
						),
					),
				);
			}
		}
		else
		{
			$_query = array(
				'query'  => array(
					'bool' => array(
						'must' => array(
							'wildcard' => array(
								'_path' => $term,
							),
						),
					),
				),
				'size'   => $size,
				'from'   => $from,
				'facets' => array(
					'paths'        => array(
						'terms' => array(
							'field' => '_path',
							'size'  => 10,
						)
					),
					'published_on' => array(
						'date_histogram' => array(
							'field'    => 'histogram_time',
							'interval' => $interval,
						)
					)
				)
			);
		}

		Log::debug( json_encode( $_query ) );

		$_query = new Query( $_query );
		$_search = new Search( $this->_client );
		$_results = $_search->addIndex( 'graylog2_0' )->search( $_query );

		return $_results;
	}

	/**
	 * @param int $from
	 * @param int $size
	 */
	public function globalStats( $from = 0, $size = 1 )
	{
		$_query = array(
			'query' => array(
				array(
					'bool' => array(
						'must' => array(
							'term' => array('message.facility' => 'cloud/cli/global/metrics'),
						),
					),
				),
			),
			'size'  => $size,
			'from'  => $from,
			'sort'  => array(
				'_timestamp' => array(
					'order' => 'desc'
				),
			),
		);

		$_query = new Query( $_query );
		$_search = new Search( $this->_client );
		$_result = $_search->addIndex( 'graylog2_0' )->search( $_query )->current()->getHit();

		return $_result['_source'];
	}

	/**
	 * @param int $from
	 * @param int $size
	 *
	 * @return array
	 */
	public function allStats( $from = 0, $size = 1 )
	{
		$_query = array(
			'query' => array(
				array(
					'bool' => array(
						'must' => array(
							'term' => array('message.facility' => 'cloud/cli/metrics'),
						),
					),
				),
			),
			'size'  => 99999999,
			'from'  => 0,
			'sort'  => array(
				'_timestamp' => array(
					'order' => 'desc'
				),
			),
		);

		$_query = new Query( $_query );
		$_search = new Search( $this->_client );

		$_result = $_search->addIndex( 'graylog2_0' )->search( $_query )->getResults();

		$_data = array();

		foreach ( $_result as $_hit )
		{
			$_data[] = $_hit->getSource();
		}

//		Log::debug( 'Result: ' . print_r( $_result, true ) );

		return $_data;
//		return $_result['_source'];
	}

	/**
	 * @param string $term
	 * @param string $value
	 * @param int    $size
	 *
	 * @return \Elastica\ResultSet
	 */
	public function termQuery( $term, $value, $size = 30 )
	{
		$_facet = new Facet\DateHistogram( 'occurred_on' );
		$_facet->setField( 'histogram_time' );
		$_facet->setInterval( 'day' );

		$_query = new Query();

		$_query->setSize( $size );
		$_query->setSort( array('histogram_time') );

		//	Filter for term
		$_filter = new Prefix( $term, $value );
		$_and = new Bool();
		$_and->addMust( $_filter );
		$_query->setFilter( $_and );

		$_query->addFacet( $_facet );

		$_search = new Search( $this->_client );

		$_results = $_search->addIndex( 'graylog2_recent' )->search( $_query );

		return $_results;
	}

	/**
	 * @return \Elastica\Client
	 */
	public function getClient()
	{
		return $this->_client;
	}
}
