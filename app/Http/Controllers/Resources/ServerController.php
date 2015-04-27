<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Server;
use DreamFactory\Library\Fabric\Database\Models\Deploy;
use DreamFactory\Library\Fabric\Database\Models\Deploy\ServerType;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;

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
        $cluster_servers = new Deploy\ClusterServer;
        $cluster_server_list = $cluster_servers->where('server_id', '=', $id)->select(['cluster_id'])->get();

        $cluster_ids = [];

        foreach($cluster_server_list as $value){
            array_push($cluster_ids, intval($value->cluster_id));
        }

        $cluster_ids = array_values($cluster_ids);

        $cluster_names = '';

        foreach($cluster_ids as $cluster_id){
            $clusters = new Deploy\Cluster;
            $cluster = $clusters->find($cluster_id);
            $cluster_names .= ' '.$cluster->cluster_id_text.',';
        }

        $cluster_names = rtrim($cluster_names, ',');

        if($cluster_names == '')
            $cluster_names = '(none)';

        $serv_t = new ServerType();
        $server_types = $serv_t->all();

        $serv = new Server;
        $server_data = $serv->find($id);

        //$config = json_decode(json_encode($server_data->config_text), true);

        if(is_array($server_data->config_text))
            $config = $server_data->config_text;
        else
            $config = json_decode($server_data->config_text, true);

        /*
        if(is_array($conf_raw)){
            $config = json_decode($conf_raw, true);
        }
        else{
            $conf_raw = (string)$conf_raw;
            $conf_mod = preg_replace("/\s+/","",$conf_raw);
            $conf_mod = preg_replace("/,}/","}",$conf_mod);
            $config = json_decode($conf_mod, true);
        }
        */

        return View::make('app.servers.edit')->with('server_id', $id)
            ->with('prefix', $this->_prefix)
            ->with('server', $server_data)
            ->with('server_types', $server_types)
            ->with('clusters', $cluster_names)
            ->with('config', $config);
    }


    public function update($id)
    {

        $server_name_text = Input::get('server_name_text');
        $server_type_select = Input::get('server_type_select');
        $server_host_text = Input::get('server_host_text');


        $settings = null;



        if($server_type_select != null){
            if($server_type_select == '1'){
                $config_text = array(
                    'port'                =>  Input::get('db_port_text'),
                    'username'            =>  Input::get('db_username_text'),
                    'password'            =>  Input::get('db_password_text'),
                    'driver'              =>  Input::get('db_driver_text'),
                    'default-database-name'     =>  Input::get('db_default_db_name_text')
                );
            }

            if($server_type_select == '2'){
                $config_text = array(
                    //'host'                =>  Input::get('web_host_text'),
                    'port'                =>  Input::get('web_port_text'),
                    'scheme'              =>  Input::get('web_scheme_text'),
                    'username'            =>  Input::get('web_username_text'),
                    'password'            =>  Input::get('web_password_text')
                );
            }

            if($server_type_select == '3'){
                $config_text = array(
                    //'host'                =>  Input::get('app_host_text'),
                    'port'                =>  Input::get('app_port_text'),
                    'scheme'              =>  Input::get('app_scheme_text'),
                    'username'            =>  Input::get('app_username_text'),
                    'password'            =>  Input::get('app_password_text'),
                    'access_token'        =>  Input::get('app_accesstoken_text')
                );
            }
        }



        $servers = new Server;
        $server = $servers->find($id);

        $server->server_type_id = intval($server_type_select);
        $server->server_id_text = $server_name_text;
        $server->host_text = $server_host_text;
        $server->config_text = json_encode($config_text);

        $server->save();

        return 'OK';


    }


    public function store()
    {



        $server_name_text = Input::get('server_name_text');
        $server_type_select = Input::get('server_type_select');
        $server_host_text = Input::get('server_host_text');


        $settings = null;



        if($server_type_select != null){
            if($server_type_select == '1'){
                $config_text = array(
                    'port'                =>  Input::get('db_port_text'),
                    'username'            =>  Input::get('db_username_text'),
                    'password'            =>  Input::get('db_password_text'),
                    'driver'           =>  Input::get('db_driver_text'),
                    'default-database-name'     =>  Input::get('db_default_db_name_text')
                );
            }

            if($server_type_select == '2'){
                $config_text = array(
                    //'host'                =>  Input::get('web_host_text'),
                    'port'                =>  Input::get('web_port_text'),
                    'scheme'              =>  Input::get('web_scheme_text'),
                    'username'            =>  Input::get('web_username_text'),
                    'password'            =>  Input::get('web_password_text')
                );
            }

            if($server_type_select == '3'){
                $config_text = array(
                    //'host'                =>  Input::get('app_host_text'),
                    'port'                =>  Input::get('app_port_text'),
                    'scheme'              =>  Input::get('app_scheme_text'),
                    'username'            =>  Input::get('app_username_text'),
                    'password'            =>  Input::get('app_password_text'),
                    'access_token'        =>  Input::get('app_accesstoken_text')
                );
            }
        }

        if(Server::where('server_id_text', '=', Input::get('server_name_text'))->exists()){
            return 'EXISTS';
        }

        $create_server = new Server;

        $create_server->server_type_id = intval($server_type_select);
        $create_server->server_id_text = $server_name_text;
        $create_server->host_text = $server_host_text;
        $create_server->config_text = json_encode($config_text);

        if($create_server->save())
            return 'OK';
        else
            return 'FAIL';

    }

    public function destroy( $ids )
    {
        $servers = new Server;

        $id_array = explode(',', $ids);

        foreach ($id_array as $id) {
            $servers->find($id)->delete();
        }

        return 'OK';
    }



    public function index()
    {
        $servers = new Server;

        return View::make('app.servers')->with('prefix', $this->_prefix)->with('servers', $servers->all());

    }


}
