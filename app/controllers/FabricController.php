<?php
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides data to the client application
 */
class FabricController extends BaseController
{
    ///
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const FABRIC_AUTH = 'fabric-auth';
    /**
     * @type string
     */
    const FABRIC_DEPLOY = 'fabric-deploy';
    /**
     * @type int
     */
    const DEFAULT_PER_PAGE = 25;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type \PDO[]
     */
    protected $_pdo;
    /**
     * @type int
     */
    protected $_limit = null;
    /**
     * @type string
     */
    protected $_order = null;
    /**
     * @type int
     */
    protected $_skip = null;
    /**
     * @type string Sort order direction
     */
    protected $_direction = null;
    /**
     * @type string
     */
    protected $_method = null;
    /**
     * @type Request
     */
    protected $_request = null;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    public function __construct()
    {
        $this->layout = null;
        $this->_request = Request::createFromGlobals();
    }

    /**
     * Returns data to a view
     */
    public function actionServers()
    {
        $this->_parseDataRequest( 's.server_type_id' );

        $_rows = DB::table( 'server_t' )
            ->select( 'server_t.id, server_t.server_type_id, server_type_t.type_name_text, server_t.host_text, server_t.lmod_date' )
            ->join( 'server_type_t', 'server_t.server_type_id', '=', 'server_type_t.id' )
            ->orderBy( $this->_order, $this->_direction )
            ->skip( $this->_skip )
            ->take( $this->_limit )
            ->get();

        return $this->_sendResponse( $_rows, Server::count() );
    }

    /**
     * @param string $serverId
     *
     * @throws \CHttpException
     */
    public function actionServer( $serverId = null )
    {
        $_update = false;
        $_view = 'form';
        $this->_modelClass = 'Cerberus\\Yii\\Models\\Deploy\\Server';

        $serverId = $serverId ?: current( array_keys( $this->getActionParams() ) );

        try
        {
            /** @type BaseFabricModel $_model */
            /** @noinspection PhpInternalEntityUsedInspection */
            $_model = $serverId ? $this->loadModel( $serverId ) : new $this->_modelClass;
        }
        catch ( CHttpException $_ex )
        {
        }

        if ( empty( $_model ) )
        {
            throw new CHttpException( 404 );
        }

        switch ( $this->_method )
        {
            case Request::METHOD_GET:
                break;

            case Request::METHOD_POST:
                $_update = true;
                break;

            case Request::METHOD_DELETE:
                break;
        }

        $this->renderPartial( $_view, array('model' => $_model, 'update' => $_update, 'form' => $this->_renderFormConfig( $_model )) );
    }

    /**
     * Returns data to a view
     */
    public function actionInstances()
    {
        $this->_parseDataRequest( 'i.create_date desc' );

        $_sql = <<<SQL
SELECT
    i.id, i.instance_name_text, c.cluster_id_text, u.email_addr_text, i.create_date, i.lmod_date
FROM
    fabric_deploy.instance_t i,
    fabric_deploy.cluster_t c,
    fabric_auth.user_t u
WHERE
    i.cluster_id = c.id AND
    i.user_id = u.id
{$this->_order}
{$this->_limit}
SQL;

        $this->_sendResponse( $this->_findAll( static::FABRIC_DEPLOY, $_sql ), Instance::model()->count() );
    }

    /**
     * Returns data to a view
     */
    public function actionUsers()
    {
        $this->_parseDataRequest( 'u.last_name_text, u.first_name_text' );

        $_sql = <<<SQL
SELECT
    u.id, u.first_name_text, u.last_name_text, u.email_addr_text, u.lmod_date
FROM
    fabric_auth.user_t u
{$this->_order}
{$this->_limit}
SQL;

        $this->_sendResponse( $this->_findAll( static::FABRIC_AUTH, $_sql ), User::model()->count() );
    }

    /**
     * Returns data to a view
     */
    public function actionClusters()
    {
        $this->_parseDataRequest( 'c.cluster_id_text' );

        $_sql = <<<SQL
SELECT
    c.id, c.cluster_id_text, c.subdomain_text, c.lmod_date
FROM
    cluster_t c
{$this->_order}
{$this->_limit}
SQL;

        $this->_sendResponse( $this->_findAll( static::FABRIC_DEPLOY, $_sql ), Cluster::model()->count() );
    }

    /**
     * @param string     $dbName
     * @param string     $sql
     * @param array|null $params
     * @param int        $pdoOptions
     *
     * @return array|bool
     */
    protected function _findAll( $dbName, $sql, $params = null, $pdoOptions = \PDO::FETCH_NUM )
    {
        return
            Sql::findAll( $sql, $params, $this->_pdo[$dbName], $pdoOptions );
    }

    /**
     * Converts data to JSON and spits it out
     *
     * @param array $data
     * @param int   $totalRows
     * @param int   $totalFiltered
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _sendResponse( $data, $totalRows = null, $totalFiltered = null )
    {
        $_recordsFiltered = (integer)( $totalFiltered ?: $totalRows );
        $data = array('data' => $data);

        $data['draw'] = (integer)IfSet::get( $_REQUEST, 'draw' );
        $data['recordsTotal'] = (integer)$totalRows;
        $data['recordsFiltered'] = $_recordsFiltered;

        return Response::json( $data );
    }

    /**
     * Parses inbound data request for limits and sort and search
     *
     * @param int|string $defaultSort Default sort column name or number
     */
    protected function _parseDataRequest( $defaultSort = 1 )
    {
        $this->_skip = IfSet::get( $_REQUEST, 'start', 0 );
        $this->_limit = IfSet::get( $_REQUEST, 'length', static::DEFAULT_PER_PAGE );
        $this->_order = null;
        $this->_direction = null;

        if ( null !== ( $_sortOrder = IfSet::get( $_REQUEST, 'order' ) ) && is_string( $_sortOrder ) )
        {
            $this->_order = $_sortOrder;
            $this->_direction = IfSet::get( $_REQUEST, 'dir', 'asc' );
        }
    }
}