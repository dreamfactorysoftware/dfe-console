<?php
use DreamFactory\Enterprise\Console\Enums\ElasticSearchIntervals;
use DreamFactory\Enterprise\Console\Providers\Elk;
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\InstanceArchive;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * DashboardController.php
 * Responds to dashboard AJAX requests
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
class DashboardController extends BaseController
{
    //*************************************************************************
    //	Constants
    //*************************************************************************

    /**
     * @var string
     */
    const DEFAULT_FACILITY = 'platform/api';
    /**
     * @var string
     */
    const BASE_STORAGE_PATH = '/data/storage';

    //********************************************************************************
    //* Methods
    //********************************************************************************

    /**
     * Returns an object with various statistics...
     */
    public function anyStats()
    {
        return array(
            'user_count' => User::count(),
            'dsp_count'  => array(
                'live' => Instance::count(),
                'dead' => InstanceArchive::count(),
            ),
            'disk_usage' => array(
                'available' => @\disk_total_space( static::BASE_STORAGE_PATH ),
                'storage'   => $this->_diskUsage( static::BASE_STORAGE_PATH ),
            )
        );
    }

    /**
     * Returns an object with various statistics...
     */
    public function postLaunch()
    {
        Instance::launch( User::findOrFail( Auth::user()->getId() ), 'gha-test1' );

        return array(
            'user_count' => User::count(),
            'dsp_count'  => array(
                'live' => Instance::count(),
                'dead' => InstanceArchive::count(),
            ),
            'disk_usage' => array(
                'available' => @\disk_total_space( static::BASE_STORAGE_PATH ),
                'storage'   => $this->_diskUsage( static::BASE_STORAGE_PATH ),
            )
        );
    }

    /**
     * Retrieves logs
     */
    public function postLogs()
    {
        $_which = trim( strtolower( Request::get( 'which', null, FILTER_SANITIZE_STRING ) ) );
        $_raw = ( 1 == Request::get( 'raw', 0, FILTER_SANITIZE_NUMBER_INT ) );
        $_facility = Request::get( 'facility', static::DEFAULT_FACILITY );
        $_interval = Request::get( 'interval', ElasticSearchIntervals::DAY );
        $_from = Request::get( 'from', 0, FILTER_SANITIZE_NUMBER_INT );
        $_size = Request::get( 'size', 30, FILTER_SANITIZE_NUMBER_INT );

        if ( $_size < 1 || $_size > 120 )
        {
            $_size = 30;
        }

        switch ( $_which )
        {
            case 'metrics':
                $_facility = 'cloud/cli/metrics';
                $_which = null;
                break;

            case 'logins':
                $_facility = 'platform/api';
                $_which = array(
                    'fabric.short_message' => 'LOGIN /web/login',
                    'fabric.method'  => 'POST',
                );
                break;

            case 'activations':
                $_facility = 'platform/api';
                $_which = 'web/activate';
                break;
        }

        $_source = $this->_elk();

        if ( false !== ( $_results = $_source->callOverTime( $_facility, $_interval, $_size, $_from, $_which ) ) )
        {
            if ( !$_raw )
            {
                $_response = array('data' => array('time' => array(), 'facilities' => array()), 'label' => 'Time');
                $_facets = $_results->getAggregations();

                if ( !empty( $_facets ) )
                {
                    /** @var $_datapoint array */
                    foreach ( IfSet::get( $_facets, 'published_on', 'buckets', array() ) as $_datapoint )
                    {
                        array_push( $_response['data']['time'], array($_datapoint['time'], $_datapoint['count']) );
                    }

                    /** @var $_datapoint array */
                    foreach ( IfSet::get( $_facets, 'facilities', 'buckets', array() ) as $_datapoint )
                    {
                        array_push( $_response['data']['facilities'], array($_datapoint['term'], $_datapoint['count']) );
                    }
                }

                return $_response;
            }

            return $_results->getResponse()->getData();
        }

        return array();
    }

    /**
     * Returns current global stats
     */
    public function getGlobalStats()
    {
        /** @type Elk $_source */
        $_source = $this->_elk();
        $_stats = $_source->globalStats();

        return array_merge(
            $_stats,
            array(
                'disk_usage' => array(
                    'available' => @\disk_total_space( static::BASE_STORAGE_PATH ),
                    'storage'   => $this->_diskUsage( static::BASE_STORAGE_PATH ),
                )
            )
        );
    }

    /**
     * Returns current global stats
     */
    public function anyAllStats()
    {
        /** @type Elk $_source */
        $_source = App::make( 'elk.service' );

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

    /**
     * @return Elk
     */
    protected function _elk()
    {
        static $_service;

        return $_service ?: $_service = App::make( 'elk.service' );
    }
}
