<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;


use DreamFactory\Enterprise\Common\Http\Controllers\BaseController;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Enums\ServerTypes;
use Illuminate\Support\Facades\View;
use DreamFactory\Library\Fabric\Database\Models\Deploy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;


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

    //******************************************************************************
    //* Traits
    //******************************************************************************
    use EntityLookup;
    //******************************************************************************
    //* Methods
    //******************************************************************************
    /**
     * @return mixed
     */


    public function create()
    {
        return View::make( 'app.clusters.create' )->with('prefix', $this->_prefix);
    }

    public function edit($id)
    {
        $_contexts = [ServerTypes::DB => 'primary', ServerTypes::WEB => 'success', ServerTypes::APP => 'warning'];
        $_cluster = $this->_findCluster( $id );
        $_clusterServers = $this->_clusterServers( $_cluster->id );
        $_data = $_dropdown = $_dropdown_all = $_ids = [];

        foreach ( $_clusterServers as $_type => $_servers )
        {
            $_serverType = ServerTypes::nameOf( $_type );
            $_serverType = strtoupper($_serverType);

            foreach ( $_servers as $_server )
            {
                $_label = <<<HTML
<div><span class="label label-{$_contexts[$_type]}">{$_serverType}</span></div>
HTML;

                $_button = <<<HTML
<button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" id="cluster_button_" onclick="removeServer({$_server->id});" value="delete" style="width: 25px"></button>
HTML;

                $_data[] = [
                    $_server->id,
                    $_button,
                    $_server->server_id_text,
                    $_label,
                ];

                $_ids[] = intval($_server->id);
            }
        }



        $_servers_all = Deploy\Server::all();

        if ( !empty( $_servers_all ) )
        {
            $_index1 = 0;
            $_index2 = 0;

            foreach ( $_servers_all as $_server )
            {
                $_type = $_server->server_type_id;
                $_serverType = ServerTypes::nameOf( $_type );
                $_serverType = strtoupper($_serverType);

                $_label = <<<HTML
<div><span class="label label-{$_contexts[$_type]}">{$_serverType}</span></div>
HTML;

                $_button = <<<HTML
<button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" id="cluster_button_" onclick="removeServer({$_server->id});" value="delete" style="width: 25px"></button>
HTML;

                if(!in_array(intval($_server->id), $_ids)){
                    $_dropdown[] = [
                        $_index1++,
                        intval( $_server->id ),
                        $_server->server_id_text,
                        strtoupper($_serverType),
                        $_label,
                        $_button
                    ];
                }

                $_dropdown_all[] = [
                    $_index2++,
                    intval( $_server->id ),
                    $_server->server_id_text,
                    strtoupper($_serverType),
                    $_label,
                    $_button
                ];

            }
        }

        return \View::make(
            'app.clusters.edit',
            [
                'cluster_id'          => $id,
                'prefix'              => $this->_prefix,
                'cluster'             => $_cluster,
                'servers'             => json_encode( $_data ),
                'server_dropdown_all' => json_encode( $_dropdown_all ),
                'server_dropdown'     => $_dropdown
            ]
        );
    }


    public function update($id)
    {
        $cluster_data = Input::all();

        $servers = $cluster_data['_server_list'];

        unset($cluster_data['_method']);
        unset($cluster_data['_token']);
        unset($cluster_data['_server_list']);

        $cluster_assigned_servers_array = [];

        if($servers != '')
            $cluster_assigned_servers_array = array_map('intval', explode(',', $servers));

        $cluster_server_list = Deploy\ClusterServer::where('cluster_id', '=', $id)
            ->select(['server_id'])
            ->get();

        $server_ids = [];

        foreach($cluster_server_list as $value){
            array_push($server_ids, intval($value->server_id));
        }

        $servers_remove = array_diff($server_ids, $cluster_assigned_servers_array);

        foreach(array_values($servers_remove) as $value) {
            Deploy\ClusterServer::where('server_id', '=', intval($value))
                ->where('cluster_id', '=', intval($id))
                ->delete();
        }

        $servers_add = array_diff($cluster_assigned_servers_array, $server_ids);

        foreach(array_values($servers_add) as $value) {
            $add = array('server_id' => intval($value), 'cluster_id' => intval($id));
            Deploy\ClusterServer::create($add);
        }


        $cluster = Deploy\Cluster::find($id);
        $cluster->update($cluster_data);

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/clusters';

        return Redirect::to($_redirect);
    }




    public function store()
    {
        $create_cluster = new Deploy\Cluster;

        $input = Input::all();

        $create_cluster->create($input);

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/clusters';

        return Redirect::to($_redirect);
    }



    public function index()
    {
        $clusters = new Deploy\Cluster;

        return View::make('app.clusters')->with('prefix', $this->_prefix)->with('clusters', $clusters->all());//take(10)->get());
    }


}
