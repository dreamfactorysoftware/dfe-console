<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use Illuminate\Support\Facades\View;
use DreamFactory\Library\Fabric\Database\Models\Deploy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;


class ClusterController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'cluster_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Cluster';
    /** @type string */
    protected $_resource = 'cluster';

    protected $_prefix = 'v1';


    public function create()
    {
        return View::make( 'app.clusters.create' )->with('prefix', $this->_prefix);
    }

    public function edit($id)
    {
        $clusters = new Deploy\Cluster;
        $cluster = $clusters->find($id);


        $cluster_servers = new Deploy\ClusterServer;
        $cluster_server_list = $cluster_servers->where('cluster_id', '=', $id)->select(['server_id'])->get();

        $server_ids = [];

        foreach($cluster_server_list as $value){
            array_push($server_ids, intval($value->server_id));
        }

        $assigned_server_ids = [];

        $servers = new Deploy\Server;
        $servers = $servers->whereIn('id', $server_ids)->select(['id', 'server_id_text', 'server_type_id'])->get();

        $servers_tabledata = [];
        foreach($servers as $value){

            $label = '';

            if($value->server_type_id == 1)
                $label = "<div><span class='label label-primary'>DB</span></div>";

            if($value->server_type_id == 2)
                $label = "<div><span class='label label-success'>WEB</span></div>";

            if($value->server_type_id == 3)
                $label = "<div><span class='label label-warning'>APP</span></div>";


            array_push($servers_tabledata, array(
                $value->id,
                "<button type='button' class='btn btn-default btn-xs fa fa-fw fa-trash' id='cluster_button_' onclick=removeServer(".$value->id.") value='delete' style='width: 25px'></button>",
                $value->server_id_text,
                $label
            ));

            array_push($assigned_server_ids, $value->id);
        }

        $servers = json_encode($servers_tabledata);

        $server_options = new Deploy\Server;
        $server_options = $server_options->all();

        $server_dropdown = [];

        foreach($server_options as $value){

            if(!in_array($value->id, $assigned_server_ids)){
                $label = '';

                if($value->server_type_id == 1){
                    $label = 'DB';
                    $label_div = "<div><span class='label label-primary'>DB</span></div>";
                }
                if($value->server_type_id == 2){
                    $label = 'WEB';
                    $label_div = "<div><span class='label label-success'>WEB</span></div>";
                }
                if($value->server_type_id == 3){
                    $label = 'APP';
                    $label_div = "<div><span class='label label-warning'>APP</span></div>";
                }

                $remove_button = "<button type='button' class='btn btn-default btn-xs fa fa-fw fa-trash' id='cluster_button_' onclick=removeServer(".$value->id.") value='delete' style='width: 25px'></button>";
                $this_id = count($server_dropdown);
                array_push($server_dropdown, array($this_id, intval($value->id), $value->server_id_text, $label, $label_div, $remove_button));
            }
        }


        return View::make('app.clusters.edit')
            ->with('cluster_id', $id)
            ->with('prefix', $this->_prefix)
            ->with('cluster', $cluster)
            ->with('servers', $servers)
            ->with('server_dropdown_str', json_encode($server_dropdown))
            ->with('server_dropdown', $server_dropdown);
    }



    public function update($id)
    {
        $cluster_name_text = Input::get('cluster_name_text');
        $cluster_subdomain_text = Input::get('cluster_subdomain_text');
        $cluster_instancecount_text = Input::get('cluster_instancecount_text');
        $cluster_assigned_servers = Input::get('cluster_assigned_servers');

        $cluster_assigned_servers_array = array_map('intval', explode(',', $cluster_assigned_servers));

        $cluster_servers = new Deploy\ClusterServer;
        $cluster_server_list = $cluster_servers->where('cluster_id', '=', $id)->select(['server_id'])->get();

        $server_ids = [];

        foreach($cluster_server_list as $value){
            array_push($server_ids, intval($value->server_id));
        }

        $servers_remove = array_diff($server_ids, $cluster_assigned_servers_array);
        $servers_remove = array_values($servers_remove);

        foreach($servers_remove as $value) {
            $cs = new Deploy\ClusterServer;
            $cs->where('server_id', '=', intval($value))->where('cluster_id', '=', intval($id))->delete();
        }

        $servers_add = array_diff($cluster_assigned_servers_array, $server_ids);
        $servers_add = array_values($servers_add);

        foreach($servers_add as $value) {
            $cs = new Deploy\ClusterServer;
            $cs->server_id = intval($value);
            $cs->cluster_id = intval($id);
            $cs->save();
        }

        $clusters = new Deploy\Cluster;
        $cluster = $clusters->find($id);

        $cluster->cluster_id_text = $cluster_name_text;
        $cluster->subdomain_text = $cluster_subdomain_text;

        $cluster->save();

        return 'OK';
    }




    public function store()
    {

        $cluster_name_text = Input::get('cluster_name_text');
        $cluster_subdomain_text = Input::get('cluster_subdomain_text');
        $cluster_instancecount_text = Input::get('cluster_instancecount_text');


        if(Deploy\Cluster::where('cluster_id_text', '=', Input::get('cluster_name_text'))->exists()){
            return 'EXISTS';
        }

        $create_cluster = new Deploy\Cluster;

        //$create_cluster->user_id = null;
        $create_cluster->cluster_id_text = $cluster_name_text;
        $create_cluster->subdomain_text = $cluster_subdomain_text;

        if($create_cluster->save())
            return 'OK';
        else
            return 'FAIL';
    }



    public function index()
    {

        $clusters = new Deploy\Cluster;

        //echo $clusters->all();

        return View::make('app.clusters')->with('prefix', $this->_prefix)->with('clusters', $clusters->all());//take(10)->get());
    }


}
