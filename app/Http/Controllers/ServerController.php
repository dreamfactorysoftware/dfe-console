<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;

class ServerController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_tableName = 'server_t';
    /**
     * @type string
     */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Server';
    /** @type string */
    protected $_resource = 'server';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    protected function _loadData()
    {
        $_columns = array('server_t.id', 'server_t.server_id_text', 'server_type_t.type_name_text', 'server_t.host_text', 'server_t.lmod_date');

        /** @type Builder $_query */
        $_query = Server::join( 'server_type_t', 'server_t.server_type_id', '=', 'server_type_t.id' )->select( $_columns );

        return $this->_processDataRequest( 'instance_t.instance_id_text', Server::count(), $_columns, $_query );
    }
}
