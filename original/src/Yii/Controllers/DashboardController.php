<?php
namespace Cerberus\Yii\Controllers;

use Cerberus\Enums\ElasticSearchIntervals;
use Cerberus\Services\GraylogData;
use Cerberus\Services\Provisioning\DreamFactory\Storage;
use Cerberus\Yii\Models\Auth\User;
use Cerberus\Yii\Models\Deploy\Instance;
use Cerberus\Yii\Models\Deploy\InstanceArch;
use DreamFactory\Yii\Controllers\DreamRestController;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Interfaces\HttpMethod;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Option;

/**
 * DashboardController.php
 * Responds to dashboard AJAX requests
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
class DashboardController extends DreamRestController
{
	//*************************************************************************
	//	Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const DEFAULT_FACILITY = 'platform/api';

	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	 * Initialize the controller
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();

		//	We are resty
		$this->setResponseFormat( static::RESPONSE_FORMAT_V2 );
		$this->layout = false;
		$this->defaultAction = false;

		//	Only deal in JSON
		$this->setContentType( 'application/json' );
	}

	/**
	 * {@InheritDoc}
	 */
	public function accessRules()
	{
		return array(
			array(
				'allow',
				'controllers' => array( 'dashboard' ),
				'users'       => array( '*' ),
				'ips'         => array( '127.0.0.1', '66.162.17.86', '50.167.198.120', '172.56.5.173' ),
				'verbs'       => array( HttpMethod::GET, HttpMethod::POST ),
			),
			//	Fail
			array(
				'deny',
			),
		);
	}

	/**
	 * Returns an object with various statistics...
	 */
	public function requestStats()
	{
		return array(
			'user_count' => User::model()->count(),
			'dsp_count'  => array(
				'live' => Instance::model()->count(),
				'dead' => InstanceArch::model()->count(),
			),
			'disk_usage' => array(
				'available' => @\disk_total_space( Storage::BASE_STORAGE_PATH ),
				'storage'   => $this->_diskUsage( Storage::BASE_STORAGE_PATH ),
			)
		);
	}

	/**
	 * Returns an object with various statistics...
	 */
	public function requestLaunch()
	{
		Instance::launch( User::model()->findByPk( Pii::user()->getId() ), 'gha-test1' );

		return array(
			'user_count' => User::model()->count(),
			'dsp_count'  => array(
				'live' => Instance::model()->count(),
				'dead' => InstanceArch::model()->count(),
			),
			'disk_usage' => array(
				'available' => @\disk_total_space( Storage::BASE_STORAGE_PATH ),
				'storage'   => $this->_diskUsage( Storage::BASE_STORAGE_PATH ),
			)
		);
	}

	/**
	 * Retrieves logs
	 */
	public function requestLogs()
	{
		$_which = trim( strtolower( FilterInput::request( 'which', null, FILTER_SANITIZE_STRING ) ) );
		$_raw = ( 1 == FilterInput::request( 'raw', 0, FILTER_SANITIZE_NUMBER_INT ) );
		$_facility = FilterInput::request( 'facility', static::DEFAULT_FACILITY );
		$_interval = FilterInput::request( 'interval', ElasticSearchIntervals::Day );
		$_from = FilterInput::request( 'from', 0, FILTER_SANITIZE_NUMBER_INT );
		$_size = FilterInput::request( 'size', 30, FILTER_SANITIZE_NUMBER_INT );

		switch ( $_which )
		{
			case 'metrics':
				$_facility = 'cloud/cli/metrics';
				$_which = null;
				break;

			case 'logins':
				$_facility = 'platform/api';
				$_which = 'web/login';
				break;

			case 'activations':
				$_facility = 'platform/api';
				$_which = 'web/activate';
				break;
		}

		$_source = new GraylogData( $this );
		$_results = $_source->callOverTime( $_facility, $_interval, $_size, $_from, $_which );
		$_facets = $_results->getFacets();

		if ( !$_raw )
		{
			$_response = array( 'data' => array( 'time' => array(), 'facilities' => array() ), 'label' => 'Time' );

			if ( !empty( $_facets ) )
			{
				/** @var $_datapoint array */
				foreach ( Option::getDeep( $_facets, 'published_on', 'entries', array() ) as $_datapoint )
				{
					array_push( $_response['data']['time'], array( $_datapoint['time'], $_datapoint['count'] ) );
				}

				/** @var $_datapoint array */
				foreach ( Option::getDeep( $_facets, 'facilities', 'terms', array() ) as $_datapoint )
				{
					array_push( $_response['data']['facilities'], array( $_datapoint['term'], $_datapoint['count'] ) );
				}
			}

			echo json_encode( $_response );
		}
		else
		{
			echo json_encode( $_results->getResponse()->getData() );
		}

		Pii::end();
	}

	/**
	 * Returns current global stats
	 */
	public function requestGlobalStats()
	{
		$_source = new GraylogData( $this );
		$_stats = $_source->globalStats();

		return array_merge(
			$_stats,
			array(
				'disk_usage' => array(
					'available' => @\disk_total_space( Storage::BASE_STORAGE_PATH ),
					'storage'   => $this->_diskUsage( Storage::BASE_STORAGE_PATH ),
				)
			)
		);
	}

	/**
	 * Returns current global stats
	 */
	public function requestAllStats()
	{
		$_source = new GraylogData( $this );

		return $_source->allStats();
	}

	/**
	 * @param string $path
	 *
	 * @return float
	 */
	protected function _diskUsage( $path )
	{
		preg_match( '/\d+/', `du -sk $path`, $_kbs );

		return round( $_kbs[0] / 1024, 1 );
	}
}
