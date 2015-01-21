<?php
use Doctrine\CouchDB\HTTP\Client;

/**
 * Gets data from the ELK system
 */
class ElkServiceProvider
{
    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var Client
     */
    protected $_client = null;
    /**
     * @type array Array of elastic search shards
     */
    protected static $_indices = array('graylog2_0', 'graylog2_1', 'graylog2_2');

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

        /** @noinspection PhpIncludeInspection */
        $_config = require_once \Kisma::get( 'app.config_path' ) . '/elasticsearch.config.php';

        if ( empty( $_config ) )
        {
            throw new \RuntimeException( 'No configuration file found for elasticsearch.' );
        }

        $this->_client = new Client( $_config );

        $this->_getIndices();
    }

    /**
     * Retrieves the indices available from ElasticSearch
     */
    protected function _getIndices()
    {
        /** @type \CCache $_cache */
        $_cache = \Yii::app()->cache;

        if ( $_cache && false !== ( $_indices = $_cache->get( 'es.indices' ) ) )
        {
            return static::$_indices = $_indices;
        }

        $_indices = array();
        $_response = $this->_client->request( '_aliases?pretty=1' );

        foreach ( $_response->getData() as $_index => $_aliases )
        {
            //  No recent index
            if ( false === stripos( $_index, '_recent' ) )
            {
                $_indices[] = $_index;
            }
        }

        if ( !empty( $_indices ) )
        {
            static::$_indices = $_indices;
            $_cache && $_cache->set( 'es.indices', $_indices, 3600 );
        }
    }

    /**
     * @param string       $facility
     * @param string       $interval
     * @param int          $size
     * @param int          $from
     * @param array|string $term
     *
     * @throws \CHttpException
     * @return \Elastica\ResultSet
     */
    public function callOverTime( $facility, $interval = 'day', $size = 30, $from = 0, $term = null )
    {
        if ( !ElasticSearchIntervals::contains( $interval ) )
        {
            throw new \InvalidArgumentException( 'The interval of "' . $interval . '" is not valid.' );
        }

        $_query = $this->_buildQuery( $facility, $interval, $size, $from, $term );

        Log::debug( json_encode( $_query ) );

        $_results = null;

        try
        {
            $_results = $this->_doSearch( $_query );
        }
        catch ( \Exception $_ex )
        {
            Log::error( 'Exception retrieving logs: ' . $_ex->getMessage() );

            throw new \CHttpException( 500, $_ex->getMessage() );
        }

        return $_results;
    }

    /**
     * @param string       $facility
     * @param string       $interval
     * @param int          $size
     * @param int          $from
     *
     * @param string|array $term
     *
     * @return array
     */
    protected function _buildQuery( $facility, $interval = 'day', $size = 30, $from = 0, $term = null )
    {
        $_query = array(
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
                        'field'    => 'message.timestamp',
                        'interval' => $interval,
                    )
                )
            )
        );

        if ( empty( $term ) )
        {
            $_query['facets']['paths'] = array(
                'terms' => array(
                    'field' => 'message.path',
                    'size'  => 10,
                )
            );

            $_query['query'] = empty( $facility )
                ? array(
                    array(
                        'match_all' => array(),
                    ),
                )
                : array(
                    'bool' => array(
                        'must' => array(
                            'wildcard' => array(
                                'message.facility' => $facility,
                            ),
                        ),
                    ),
                );
        }
        else
        {
            if ( is_array( $term ) )
            {
                $_query['query'] = array('bool' => array('should' => array()));

                foreach ( $term as $_field => $_value )
                {
                    $_query['query']['bool']['should'][] = array('wildcard' => array($_field => $_value));
                }
            }
            else
            {
                $_query['query'] =
                    array(
                        'bool' => array(
                            'must' => array(
                                'wildcard' => array(
                                    'message.path' => $term,
                                ),
                            ),
                        ),
                    );
            }

        }

        return $_query;
    }

    /**
     * @param string|Query $query
     * @param string|array $indices
     *
     * @return \Elastica\ResultSet
     */
    protected function _doSearch( $query, $indices = null )
    {
        $_results = false;

        $_query = !( $query instanceof Query ) ? new Query( $query ) : $query;
        $_search = new Search( $this->_client );

        $indices = $indices ?: static::$_indices;

        if ( null !== $indices )
        {
            if ( !is_array( $indices ) )
            {
                $indices = array($indices);
            }

            $_search->addIndices( $indices );
        }

        try
        {
            $_results = $_search->search( $_query );
        }
        catch ( PartialShardFailureException $_ex )
        {
            //Log::info( 'Partial shard failure. ' . $_ex->getMessage() . ' failed shard(s).' );

            return new ResultSet( $_ex->getResponse(), $_query );
        }
        catch ( \Exception $_ex )
        {
            Log::error( $_ex->getMessage() );
        }

        return $_results;

    }

    /**
     * @param int $from
     * @param int $size
     */
    public
    function globalStats( $from = 0, $size = 1 )
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
                'message.timestamp' => array(
                    'order' => 'desc'
                ),
            ),
        );

        $_query = new Query( $_query );
        $_search = new Search( $this->_client );

        try
        {
            $_result = $_search->search( $_query )->current()->getHit();
        }
        catch ( PartialShardFailureException $_ex )
        {
            Log::info( 'Partial shard failure: ' . $_ex->getMessage() . ' failed shard(s).' );
            $_response = $_ex->getResponse()->getData();

            return $_response['hits']['hits'][0]['_source'];
        }

        return $_result['_source'];
    }

    /**
     * @param int $from
     * @param int $size
     *
     * @return array
     */
    public
    function allStats( $from = 0, $size = 1 )
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
                'message.timestamp' => array(
                    'order' => 'desc'
                ),
            ),
        );

        $_query = new Query( $_query );
        $_search = new Search( $this->_client );
        $_result = $_search->search( $_query )->getResults();

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
    public
    function termQuery( $term, $value, $size = 30 )
    {
        $_facet = new Facet\DateHistogram( 'occurred_on' );
        $_facet->setField( 'message.timestamp' );
        $_facet->setInterval( 'day' );

        $_query = new Query();
        $_query->setSize( $size );
        $_query->setSort( array('message.timestamp') );

        //	Filter for term
        $_filter = new Prefix( $term, $value );
        $_and = new Bool();
        $_and->addMust( $_filter );
        $_query->setPostFilter( $_and );
        $_query->addFacet( $_facet );

        $_results = $this->_doSearch( $_query );

        return $_results;
    }

    /**
     * @return Client
     */
    public
    function getClient()
    {
        return $this->_client;
    }
}
