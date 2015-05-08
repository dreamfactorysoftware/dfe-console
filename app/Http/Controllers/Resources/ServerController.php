<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;


use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Fabric\Database\Models\Deploy;
use DreamFactory\Library\Fabric\Database\Models\Deploy\ServerType;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\View;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;


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

    protected $_prefix = 'v1';

    //******************************************************************************
    //* Traits
    //******************************************************************************
    use EntityLookup;


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


    public function create()
    {
        $serv_t = new ServerType();
        $server_types = $serv_t->all();

        return View::make( 'app.servers.create' )->with('prefix', $this->_prefix)->with('server_types', $server_types);
    }


    public function edit($id)
    {
        $cluster_servers = $this->_serverClusters($id);

        $cluster_names = '';

        foreach($cluster_servers as $value){
            $cluster = $this->_findCluster($value->cluster_id);
            $cluster_names .= ' '.$cluster->cluster_id_text.',';
        }

        $cluster_names = rtrim($cluster_names, ',');
        $cluster_names = ($cluster_names ? $cluster_names : '(none)');

        $server_types = ServerType::all();
        $server_data = $this->_findServer($id);

        if(is_array($server_data->config_text))
            $config = $server_data->config_text;
        else
            $config = json_decode($server_data->config_text, true);

        return View::make('app.servers.edit')->with('server_id', $id)
            ->with('prefix', $this->_prefix)
            ->with('server', $server_data)
            ->with('server_types', $server_types)
            ->with('clusters', $cluster_names)
            ->with('config', $config);
    }


    public function update($id)
    {

        $test = Input::all();

        $type = $test['server_type_select'];
        $test1 = $test['config'][$type];
        $test['config_text'] = $test1;

        if($type == 'db') $test['server_type_id'] = 1;
        if($type == 'web') $test['server_type_id'] = 2;
        if($type == 'app') $test['server_type_id'] = 3;

        unset($test['_method']);
        unset($test['_token']);
        unset($test['config']);
        unset($test['server_type_select']);

        $server = Server::find($id);
        $server->update($test);

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/servers';

        return Redirect::to($_redirect);

    }


    public function store()
    {

        $test = Input::all();

        $type = $test['server_type_select'];
        $test1 = $test['config'][$type];
        $test['config_text'] = $test1;

        if($type == 'db') $test['server_type_id'] = 1;
        if($type == 'web') $test['server_type_id'] = 2;
        if($type == 'app') $test['server_type_id'] = 3;

        unset($test['_method']);
        unset($test['_token']);
        unset($test['config']);
        unset($test['server_type_select']);

        $create_server = new Deploy\Server;
        $create_server->create($test);

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/servers';

        return Redirect::to($_redirect);

    }

    public function destroy( $ids )
    {

        $id_array = [];

        if($ids == 'multi') {
            $params = Input::all();
            $selected = $params['_selected'];
            $id_array = explode(',', $selected);
        }
        else{
            $id_array = explode(',', $ids);
        }
        
        foreach ($id_array as $id) {
            Server::find($id)->delete();
            Deploy\ClusterServer::where('server_id', '=', intval($id))->delete();
        }

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/servers';

        return Redirect::to($_redirect);
    }



    public function index()
    {
        $servers = new Server;

        return View::make('app.servers')->with('prefix', $this->_prefix)->with('servers', $servers->all());

    }


}
