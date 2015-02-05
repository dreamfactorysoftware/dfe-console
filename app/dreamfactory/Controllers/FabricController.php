<?php
namespace DreamFactory\Enterprise\Console\Controllers;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides data to the client application
 */
class FabricController extends FactoryController
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServers()
    {
        $this->_parseDataRequest( 's.server_type_id' );

        $_sql = <<<SQL
SELECT
    s.id, s.server_type_id, t.type_name_text, s.host_text, s.lmod_date
FROM
    server_t s,
    server_type_t t
WHERE
    s.server_type_id = t.id
{$this->_order}
{$this->_limit}
SQL;

        $_data = DB::select( DB::raw( $_sql ) );

        if ( !empty( $_data ) )
        {
            return $this->_sendResponse( $_data, Server::count() );
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServer( $id = null )
    {
        $_sql = <<<SQL
SELECT
    *
FROM
    server_t
WHERE
    id = {$id}
SQL;

        return $this->_sendResponse( DB::select( DB::raw( $_sql ) ) );
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
        //  Don't wrap if there are no totals
        if ( null === $totalRows && null === $totalFiltered )
        {
            return Response::json( $data );
        }

        $_recordsFiltered = (integer)( $totalFiltered ?: 0 );
        $data = array('data' => $data);

        $data['draw'] = (integer)IfSet::get( $_REQUEST, 'draw' );
        $data['recordsTotal'] = (integer)$totalRows;
        $data['recordsFiltered'] = $_recordsFiltered;

        return Response::json( $data );
    }

    /**
     * Converts data to JSON and spits it out
     *
     * @param array $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _sendSingleResponse( $data )
    {
        //  Unwrap data if single row
        if ( is_array( $data ) && count( $data ) === 1 )
        {
            $data = array_shift( $data );
        }

        return Response::json( $data );
    }
}
